<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    private function generateToken(array $data)
    {
        $client_id = config('services.base_auth.client_id');
        $client_secret = config('services.base_auth.client_secret');
        $http = new \GuzzleHttp\Client;
        $response = $http->post(url('/oauth/token'), [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'username' => $data['email'],
                'password' => $data['password'],
                'scope' => '',
            ],
        ]);
       
        return json_decode((string) $response->getBody(), true);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $credentials = $request->only('email', 'password');
            return response()->json($this->generateToken($credentials));
        } catch(\GuzzleHttp\Exception\ClientException $e) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')]
            ]);
        }
    }

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check user role
        $user = \App\Models\User::whereEmail($request->email)->first();
        if ($user && $user->sys_role === 'user') {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        try {
            $credentials = $request->only('email', 'password');

            return response()->json($this->generateToken($credentials));
        } catch(\GuzzleHttp\Exception\ClientException $e) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }
    }
}
