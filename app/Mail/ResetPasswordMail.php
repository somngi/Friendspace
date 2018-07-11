<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name,$activation_token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name,$activation_token)
    {
        //
        $this->name = $name;
        $this->activation_token = $activation_token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('email.reset_password')
            ->subject("Reset Password")
            ->from("friendspace779@gmail.com","FriendSpace App");
    }
}
