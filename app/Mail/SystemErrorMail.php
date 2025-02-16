<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemErrorMail extends Mailable
{
    use Queueable, SerializesModels;

    public $errorMessage;
    public $errorContext;

    public function __construct($errorMessage, $errorContext)
    {
        $this->errorMessage = $errorMessage;
        $this->errorContext = $errorContext;
    }

    public function build()
    {
        return $this->subject('System Error Notification')
            ->view('emails.system_error')
            ->with([
                'message' => $this->errorMessage,
                'context' => $this->errorContext,
            ]);
    }
}
