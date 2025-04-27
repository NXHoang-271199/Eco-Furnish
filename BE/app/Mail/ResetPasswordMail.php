<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $token)
    {
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $resetUrl = URL::signedRoute('admin.password.reset', [
            'token' => $this->token,
            'email' => $this->user->email
        ]);

        return $this->subject('Đặt lại mật khẩu - ' . config('app.name'))
                    ->view('emails.reset_password')
                    ->with([
                        'resetUrl' => $resetUrl,
                        'user' => $this->user
                    ]);
    }
} 