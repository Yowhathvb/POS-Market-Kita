<?php

namespace App\Mail\Transports;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class BrevoTransport extends AbstractTransport
{
    public function __construct(
        protected ?string $apiKey = null,
    ) {
        parent::__construct();
    }

    /**
     * Kirim email melalui Brevo Transactional Email HTTPS API (kompatibel dengan Wasmer tanpa SMTP).
     */
    protected function doSend(SentMessage $message): void
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('BREVO_API_KEY belum diisi di file .env.');
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $fromAddresses = $email->getFrom();
        $senderAddress = $fromAddresses[0] ?? null;

        $sender = [
            'email' => $senderAddress?->getAddress() ?: config('mail.from.address', 'hello@example.com'),
            'name' => $senderAddress?->getName() ?: config('mail.from.name', config('app.name', 'POS Market')),
        ];

        $payload = [
            'sender' => $sender,
            'to' => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject() ?? '',
        ];

        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->formatAddresses($cc);
        }

        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->formatAddresses($bcc);
        }

        if ($replyTo = $email->getReplyTo()) {
            $firstReplyTo = $replyTo[0];
            $payload['replyTo'] = array_filter([
                'email' => $firstReplyTo->getAddress(),
                'name' => $firstReplyTo->getName() ?: null,
            ]);
        }

        $htmlBody = $email->getHtmlBody();
        $textBody = $email->getTextBody();

        if (!empty($htmlBody)) {
            $payload['htmlContent'] = is_string($htmlBody) ? $htmlBody : (string) $htmlBody;
        }

        if (!empty($textBody)) {
            $payload['textContent'] = is_string($textBody) ? $textBody : (string) $textBody;
        }

        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(15)->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new \RuntimeException('Brevo API error (' . $response->status() . '): ' . $response->body());
        }
    }

    /**
     * Format daftar alamat email Symfony ke format array Brevo.
     *
     * @param Address[] $addresses
     * @return array<int, array<string, string>>
     */
    protected function formatAddresses(array $addresses): array
    {
        return array_values(array_map(function (Address $address) {
            return array_filter([
                'email' => $address->getAddress(),
                'name' => $address->getName() ?: null,
            ]);
        }, $addresses));
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
