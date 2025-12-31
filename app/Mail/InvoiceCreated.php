<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $invoice;
    public $pdfContent;
    public $paymentLink;

    /**
     * Create a new message instance.
     */
    public function __construct($invoice, $pdfContent = null, $paymentLink = null)
    {
        $this->invoice = $invoice;
        $this->pdfContent = $pdfContent;
        $this->paymentLink = $paymentLink; // Pass the link or fallback to invoice attribute
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Invoice from ' . ($this->invoice->branch->business->name ?? 'POS Supermarket'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoice.created',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        if ($this->pdfContent) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromData(
                    fn () => $this->pdfContent,
                    "Invoice-{$this->invoice->invoice_number}.pdf"
                )->withMime('application/pdf'),
            ];
        }
        return [];
    }
}
