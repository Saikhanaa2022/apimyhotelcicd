<?php

namespace App\Http\Controllers;

use App\Events\UserInvited;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, Validator};
use App\Traits\{BelongsToAdmin};
use App\Notifications\TestNotification;

class UserController extends BaseController
{
    use BelongsToAdmin;

    /**
     * The model associated with the controller.
     *
     * @var string
     */
    protected $model = 'App\Models\User';
    protected $request = 'App\Http\Requests\UserRequest';

    /**
     * Get a validator for an incoming request.
     *
     * @param  array  $data
     * @param  string  $action
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator($data, $action, $messages = [])
    {
        Validator::make($data, call_user_func([$this->request, $action]), [
            'password.regex' => 'Нууц үг үсэг, тоо, тэмдэгт оруулсан байх шаардлагатай.'
        ])->validate();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newQuery()
    {
        $hotelId = request()->hotel->id;
        return $this->model::whereHas('hotels', function ($query) use ($hotelId) {
            $query->where('hotels.id', $hotelId);
        });
    }

    /**
     * Request parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function requestParams(Request $request)
    {
        $data = $request->only([
            'id', 'name', 'position', 'phoneNumber', 'email',
        ]);

        if (!$request->filled('id')) {
            $data = array_merge($data, [
                'hotelId' => $request->hotel->id,
            ]);
        }

        $data = array_merge($data, [
            'roleId' => $request->input('role.id'),
            'password' => Hash::make($request->input('password')),
        ]);

        return $data;
    }

    /**
     * After new resource created.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    protected function afterCommit(Request $request, $model)
    {
        if (!$request->filled('id')) {
            // Sync user to hotel
            $model->hotels()->sync($request->hotel);
            event(new UserInvited($model));
        }
    }

    /**
     * Update default user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDefaultUser(Request $request)
    {
        $request->validate([
            'hotelId' => 'nullable',
            'id' => 'required|integer'
        ]);

        // Find new default user
        $newUser = $this->model::findOrFail($request->input('id'));

        // Old default user
        $oldUser = $request->user();

        if ($oldUser->is_default && $newUser->id != $oldUser->id) {
            $oldUser->is_default = 0;
            $oldUser->save();

            $newUser->is_default = 1;
            $newUser->save();

            // Sync old user hotels to new user hotels
            $hotelsId = $oldUser->hotels()->pluck('hotels.id');
            $newUser->hotels()->sync($hotelsId, false);

            return response()->json([
                'success' => true,
            ]);
        }

        return response()->json([
            'message' => 'Үйлдэл амжилтгүй боллоо.',
        ], 400);
    }

    /**
     * User change own password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'oldPassword' => 'required',
            'password' => 'required|string|min:6|same:passwordConfirmation|regex:/^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[.#?!@$%^&*-]).{6,}$/'
        ], [
            'password.regex' => 'Нууц үг үсэг, тоо, тэмдэгт оруулсан байх шаардлагатай.'
        ]);

        $user = $request->user();

        // Old password check
        $isValid = Hash::check($request->oldPassword, $user->password);
        if (!$isValid) {
            return response()->json(['errors' => [
                'oldPassword' => ["Өмнөх нууц үг буруу байна."],
            ]], 422);
        }

        // Get new password
        $user->password = Hash::make($request->password);
        $user->save();

        // Send email when user change his password
        // event(new PasswordChanged($user));

        return response()->json([
            'message' => trans('passwords.reset'),
        ]);
    }

    /**
     * Update user from admin.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUser(Request $request)
    {
        $request->validate([
            'sysRole' => 'nullable|string|in:user,admin',
            'password' => 'nullable|string|min:6|regex:/^(?=.*?[a-z])(?=.*?[0-9])(?=.*?[.#?!@$%^&*-]).{6,}$/'
        ], [
            'sysRole.in' => 'Хэрэглэгчийн эрх буруу байна.',
            'password.regex' => 'Нууц үг үсэг, тоо, тэмдэгт оруулсан байх шаардлагатай.'
        ]);

        $params = $request->only([
            'id', 'sysRole', 
        ]);

        if ($request->filled('password')) {
            $params = array_merge($params, [
                'password' => Hash::make($request->input('password')),
            ]);
        }

        $user = $this->model::find($request->input('id'));
        $user->update(snakeCaseKeys($params));

        // if ($request->filled('password')) {
            // Send email when user password changed
            // event(new PasswordChanged($user));
        // }

        return response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Send notification to specified users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotification(Request $request)
    {
        // Validate request
        $this->validator($request->all(), 'sendNotificationRules');

        $type = $request->input('type', 'message');
        $message = $request->input('message');
        $event = $request->input('event');
        $notifyDataId = $request->input('notifyDataId');

        if ($type === 'reservation' && $event === 'created') {
            $message = 'Шинэ захиалга орж ирлээ.';
        } else if ($type === 'resRequest' && $event === 'created') {
            $message = 'Шинэ захиалгын хүсэлт орж ирлээ.';
        }

        $notifyData = [
            'message' => $message,
            'event' => $event,
            'type' => $type,
            'dataId' => $notifyDataId
        ];

        $users = $this->model::whereIn('email', $request->input('emails'))
            ->get();

        // foreach ($users as $user) {
        //     $user->notify(new TestNotification($request->input('notifyType'), $notifyData));
        // }

        return response()->json([
            'status' => true,
        ]);
    }
}
