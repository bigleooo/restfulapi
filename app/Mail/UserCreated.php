<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;                               // Laravel is gonna automatically pass the ' $user '  variable inside the view in our build() method below. LaraCasts says every public property you assign to the mailable class, will be immediately available to the view.

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()                         // The build method is gonna be executed automatically by Laravel when we are sending an instance of this mailable as an email.
    {
        return $this->markdown('emails.welcome')->subject('Please confirm your account');
    }
}
