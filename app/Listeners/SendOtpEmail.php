<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\OtpMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\ResponseTrait;

class SendOtpEmail
{
    use ResponseTrait;
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
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;
        $otp = $user->OTP = rand(10000, 99999);
        $user->save();
        $mail = Mail::to($user->email)->send(new OtpMail($otp));
    }
}
