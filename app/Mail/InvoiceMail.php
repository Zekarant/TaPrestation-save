<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $attachmentPath;

    /**
     * Create a new message instance.
     */
    public function __construct($invoice, $attachmentPath = null)
    {
        $this->invoice = $invoice;
        $this->attachmentPath = $attachmentPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Votre facture #' . ($this->invoice->number ?? $this->invoice->id))
            ->view('emails.invoice')
            ->with(['invoice' => $this->invoice]);

        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $mail->attach($this->attachmentPath, [
                'as' => basename($this->attachmentPath),
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
