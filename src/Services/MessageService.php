<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\Client;
use Aghfatehi\WhatsApp\Contracts\MessageService as MessageServiceContract;

class MessageService implements MessageServiceContract
{
    public function __construct(private Client $client) {}

    public function sendText(string $to, string $body, bool $previewUrl = false): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => $previewUrl,
                'body' => $body,
            ],
        ];

        return $this->send($payload);
    }

    public function sendMedia(string $to, string $type, string $mediaUrl, ?string $caption = null, ?string $filename = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => $type,
            $type => array_filter([
                'link' => $mediaUrl,
                'caption' => $caption,
                'filename' => $filename,
            ]),
        ];

        return $this->send($payload);
    }

    public function sendMediaById(string $to, string $type, string $mediaId, ?string $caption = null, ?string $filename = null): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => $type,
            $type => array_filter([
                'id' => $mediaId,
                'caption' => $caption,
                'filename' => $filename,
            ]),
        ];

        return $this->send($payload);
    }

    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en', array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        if (!empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->send($payload);
    }

    public function sendInteractive(string $to, array $interactiveData): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'interactive',
            'interactive' => $interactiveData,
        ];

        return $this->send($payload);
    }

    public function sendLocation(string $to, float $latitude, float $longitude, string $name = '', string $address = ''): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'location',
            'location' => array_filter([
                'latitude' => $latitude,
                'longitude' => $longitude,
                'name' => $name,
                'address' => $address,
            ]),
        ];

        return $this->send($payload);
    }

    public function sendContacts(string $to, array $contacts): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'contacts',
            'contacts' => $contacts,
        ];

        return $this->send($payload);
    }

    public function sendReaction(string $to, string $messageId, string $emoji): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'reaction',
            'reaction' => [
                'message_id' => $messageId,
                'emoji' => $emoji,
            ],
        ];

        return $this->send($payload);
    }

    public function markAsRead(string $messageId): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId,
        ];

        return $this->client->post($this->endpoint(), $payload)->data();
    }

    private function send(array $payload): array
    {
        return $this->client->post($this->endpoint(), $payload)->data();
    }

    private function endpoint(): string
    {
        return $this->client->getPhoneNumberId() . '/messages';
    }
}
