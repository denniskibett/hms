<?php

namespace App\Mail;

use App\Helpers\SystemHelper;
use App\Models\GuestMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * A guest message sent by email: the same text as the SMS, in the hotel's
 * plain letterhead.
 */
class GuestMessageMail extends Mailable
{
    public function __construct(public GuestMessage $guestMessage)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->guestMessage->subject ?: 'A message from ' . SystemHelper::appName());
    }

    public function content(): Content
    {
        return new Content(view: 'emails.guest-message', text: 'emails.guest-message-text');
    }
}
