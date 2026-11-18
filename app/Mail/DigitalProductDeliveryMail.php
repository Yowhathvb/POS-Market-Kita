<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DigitalProductDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public string $productName;
    public ?string $downloadUrl;
    public string $customerName;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['items.product', 'seller']);
        $item = $this->transaction->items->first();
        $this->productName = $item?->product?->name ?? 'Produk Digital POS Market';
        $this->downloadUrl = $this->transaction->getMagicLinkUrl();
        $this->customerName = $this->transaction->value['customer_name'] ?? 'Pelanggan Setia';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pengiriman Produk Digital: {$this->productName} (Invoice #{$this->transaction->invoice_number})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.digital_product_delivery',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
