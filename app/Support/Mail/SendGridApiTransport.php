<?php

namespace App\Support\Mail;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class SendGridApiTransport extends AbstractTransport
{
    public function __construct(private readonly string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $from = $email->getFrom()[0] ?? throw new RuntimeException('SendGrid: message has no From address.');

        $payload = [
            'personalizations' => [[
                'to' => array_map($this->formatAddress(...), $email->getTo()),
                'subject' => $email->getSubject() ?? '',
            ]],
            'from' => $this->formatAddress($from),
            'content' => array_values(array_filter([
                $email->getTextBody() ? ['type' => 'text/plain', 'value' => $email->getTextBody()] : null,
                $email->getHtmlBody() ? ['type' => 'text/html', 'value' => $email->getHtmlBody()] : null,
            ])),
        ];

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->post('https://api.sendgrid.com/v3/mail/send', $payload);

        if ($response->failed()) {
            throw new RuntimeException('SendGrid API request failed: '.$response->status().' '.$response->body());
        }
    }

    private function formatAddress(Address $address): array
    {
        return array_filter([
            'email' => $address->getAddress(),
            'name' => $address->getName() ?: null,
        ]);
    }

    public function __toString(): string
    {
        return 'sendgrid+api';
    }
}
