<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\WebhookService as WebhookServiceContract;
use Illuminate\Support\Facades\Event;
use Psr\Log\LoggerInterface;

class WebhookService implements WebhookServiceContract
{
    public function __construct(
        private array $config,
        private ?LoggerInterface $logger = null
    ) {}

    public function verifyToken(string $mode, string $token, string $challenge): mixed
    {
        if ($mode !== 'subscribe') {
            return false;
        }

        $verifyToken = $this->config['webhook']['verify_token'] ?? $this->config['verify_token'] ?? '';

        if ($token !== $verifyToken) {
            if ($this->logger) {
                $this->logger->warning('WhatsApp webhook verify token mismatch');
            }
            return false;
        }

        if ($this->logger) {
            $this->logger->info('WhatsApp webhook verified successfully');
        }

        return $challenge;
    }

    public function verifySignature(string $body, string $signature): bool
    {
        $appSecret = $this->config['app_secret'] ?? '';

        if (empty($appSecret) || empty($signature)) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $body, $appSecret);

        return hash_equals($expected, $signature);
    }

    public function parsePayload(array $payload): array
    {
        $entry = $payload['entry'][0] ?? [];
        $change = $entry['changes'][0] ?? [];
        $value = $change['value'] ?? [];

        $messages = $value['messages'] ?? [];
        $contacts = $value['contacts'] ?? [];
        $statuses = $value['statuses'] ?? [];
        $metadata = [
            'phone_number_id' => $value['metadata']['phone_number_id'] ?? null,
            'display_phone_number' => $value['metadata']['display_phone_number'] ?? null,
        ];

        $parsed = [
            'object' => $payload['object'] ?? '',
            'entry_id' => $entry['id'] ?? '',
            'time' => $entry['time'] ?? 0,
            'metadata' => $metadata,
            'messages' => [],
            'statuses' => [],
        ];

        foreach ($messages as $msg) {
            $parsed['messages'][] = $this->parseMessage($msg, $contacts);
        }

        foreach ($statuses as $status) {
            $parsed['statuses'][] = $this->parseStatus($status);
        }

        return $parsed;
    }

    public function handle(array $payload): void
    {
        $parsed = $this->parsePayload($payload);

        foreach ($parsed['messages'] as $message) {
            Event::dispatch('whatsapp.message.received', [$message]);
        }

        foreach ($parsed['statuses'] as $status) {
            $eventName = match ($status['status']) {
                'sent' => 'whatsapp.message.sent',
                'delivered' => 'whatsapp.message.delivered',
                'read' => 'whatsapp.message.read',
                'failed' => 'whatsapp.message.failed',
                default => 'whatsapp.message.status',
            };
            Event::dispatch($eventName, [$status]);
        }
    }

    private function parseMessage(array $msg, array $contacts): array
    {
        $from = $msg['from'] ?? '';
        $contact = collect($contacts)->firstWhere('wa_id', $from);

        return [
            'id' => $msg['id'] ?? '',
            'from' => $from,
            'from_name' => $contact['profile']['name'] ?? '',
            'timestamp' => (int) ($msg['timestamp'] ?? 0),
            'type' => $msg['type'] ?? 'text',
            'content' => $this->extractContent($msg),
            'context' => $msg['context'] ?? null,
        ];
    }

    private function parseStatus(array $status): array
    {
        return [
            'id' => $status['id'] ?? '',
            'message_id' => $status['message_id'] ?? $status['recipient_id'] ?? '',
            'status' => $status['status'] ?? '',
            'timestamp' => (int) ($status['timestamp'] ?? 0),
            'recipient_id' => $status['recipient_id'] ?? '',
            'pricing' => $status['pricing'] ?? null,
            'conversation' => $status['conversation'] ?? null,
            'errors' => $status['errors'] ?? [],
        ];
    }

    private function extractContent(array $msg): array
    {
        $type = $msg['type'] ?? 'text';

        return match ($type) {
            'text' => ['body' => $msg['text']['body'] ?? ''],
            'image', 'document', 'video', 'audio' => [
                'id' => $msg[$type]['id'] ?? '',
                'mime_type' => $msg[$type]['mime_type'] ?? '',
                'sha256' => $msg[$type]['sha256'] ?? '',
                'caption' => $msg[$type]['caption'] ?? '',
                'filename' => $msg[$type]['filename'] ?? '',
            ],
            'location' => [
                'latitude' => $msg['location']['latitude'] ?? 0,
                'longitude' => $msg['location']['longitude'] ?? 0,
                'name' => $msg['location']['name'] ?? '',
                'address' => $msg['location']['address'] ?? '',
            ],
            'contacts' => $msg['contacts'] ?? [],
            'interactive' => $msg['interactive'] ?? [],
            'button' => [
                'text' => $msg['button']['text'] ?? '',
                'payload' => $msg['button']['payload'] ?? '',
            ],
            'order' => $msg['order'] ?? [],
            'system' => $msg['system'] ?? [],
            'unknown' => $msg,
            default => $msg,
        };
    }
}
