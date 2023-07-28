<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
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
        $this->middleware('guest');
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|regex:/^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
        ]);

        $passwordReset = DB::table(config('auth.passwords.users.table'))
            ->where('email', $request->input('email'))
            ->first();

        if (!$passwordReset) {
            return $this->invalidToken();
        }

        $isValid = Hash::check($request->input('token'), $passwordReset->token);
        $notAvailable = Carbon::parse($passwordReset->created_at)
            ->addMinutes(config('auth.passwords.users.expire'))
            ->isPast();

        if (!$isValid || $notAvailable) {
            return $this->invalidToken();
        }

        $user = User::where('email', $passwordReset->email)
            ->firstOrFail();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        DB::table(config('auth.passwords.users.table'))
            ->where('email', $request->input('email'))
            ->delete();

        event(new PasswordReset($user));

        return response()->json([
            'message' => trans('passwords.reset'),
        ]);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidToken()
    {
        return response()->json(['errors' => [
            'token' => trans('passwords.token'),
        ]], 422);
    }
}
