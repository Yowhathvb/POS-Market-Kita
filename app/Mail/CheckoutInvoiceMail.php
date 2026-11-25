<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public string $productName;
    public string $customerName;
    public string $paymentUrl;
    public ?string $qrisUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction->load(['items.product', 'seller', 'latestPayment']);
        $item = $this->transaction->items->first();
        $this->productName = $item?->product?->name ?? ($item?->value['product_name'] ?? 'Produk Digital POS Market');
        $this->customerName = $this->transaction->value['customer_name'] ?? 'Pelanggan';
        $this->paymentUrl = route('checkout.invoice', $this->transaction->invoice_number);
        $this->qrisUrl = $this->transaction->latestPayment?->value['qr_code_url'] ?? null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tagihan Pembayaran: {$this->productName} (Invoice #{$this->transaction->invoice_number})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checkout_invoice',
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
