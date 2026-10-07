<?php
namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class StoreEmailMessageID
{
    public function handle(MessageSent $event)
    {
        // Retrieve the recipient email
        $recipient = optional($event->sent->getOriginalMessage()->getTo())[0]->getAddress() ?? null;

        // Retrieve the Message-ID from the event
        $messageID = $event->sent->getMessageId(); // ✅ Correct way to get Message-ID
dump($messageID);
        // Debugging log (optional)
        Log::info('Email Sent', [
            'recipient' => $recipient,
            'message_id' => $messageID
        ]);

        if ($recipient && $messageID) {
            // Store the Message-ID in Laravel Cache for 1 hour
            Cache::put("thread_message_id_$recipient", $messageID, 3600);
        }
    }
}
