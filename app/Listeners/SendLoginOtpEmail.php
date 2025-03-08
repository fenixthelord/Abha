<?php

namespace App\Listeners;

use App\Events\UserLogin;
use App\Mail\OtpMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendLoginOtpEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UserLogin $event): void
    {
        $user = $event->user;

        $mail = Mail::to($user->email)->send(new OtpMail($user->otp));
    }
}
