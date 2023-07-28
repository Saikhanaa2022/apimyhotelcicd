<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends VerifyEmailNotification
{
    // protected function verificationUrl($notifiable)
    // {
    //     return env('EMAIL_VERIFY_URL') . http_build_query(
    //         [
    //             'verifyLink' => URL::temporarySignedRoute(
    //                 'verification.verify',
    //                 Carbon::now()->addMinutes(60),
    //                 ['id' => $notifiable->getKey()]
    //             )
    //         ]
    //     );
    // }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        // $prefix = env('EMAIL_VERIFY_URL') . config('frontend.email_verify_url');
        $prefix = config('services.dashboard.emailVerifyUrl');

        $temporarySignedURL = URL::temporarySignedRoute(
            'verification.verify', Carbon::now()->addMinutes(60), ['id' => $notifiable->getKey()]
        );

        // I use urlencode to pass a link to my frontend.
        return $prefix . urlencode($temporarySignedURL);
    }
}
