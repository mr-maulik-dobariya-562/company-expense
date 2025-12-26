<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ThreadedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageBody;
    public $subjectLine;
    public $originalMessageID;

    public function __construct($subject, $messageBody, $originalMessageID = null)
    {
        $this->subjectLine = $subject;
        $this->messageBody = $messageBody;
        $this->originalMessageID = $originalMessageID;
    }

    public function build()
    {
        $email = $this->subject($this->subjectLine)
                      ->view('emails.threaded')
                      ->with([
                          'messageBody' => $this->messageBody
                      ]);

        // Threading logic
        if ($this->originalMessageID) {
            $email->withSymfonyMessage(function ($message) {
                $headers = $message->getHeaders();
                $headers->addTextHeader('In-Reply-To', $this->originalMessageID);
                $headers->addTextHeader('References', $this->originalMessageID);
            });
        }

        return $email;
    }
}
