<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\Client;
use Aghfatehi\WhatsApp\Contracts\MediaService as MediaServiceContract;

class MediaService implements MediaServiceContract
{
    public function __construct(private Client $client) {}

    public function upload(string $filePath, string $type): array
    {
        $phoneNumberId = $this->client->getPhoneNumberId();

        $response = $this->client->postForm("{$phoneNumberId}/media", [
            'messaging_product' => 'whatsapp',
        ], [
            'file' => [
                'path' => $filePath,
                'filename' => basename($filePath),
            ],
            'type' => $type,
        ]);

        return $response->data();
    }

    public function getUrl(string $mediaId): ?string
    {
        $response = $this->client->get($mediaId);

        return $response->get('url');
    }

    public function download(string $mediaId): ?string
    {
        $url = $this->getUrl($mediaId);
        if (!$url) {
            return null;
        }

        $response = $this->client->get(str_replace($this->client->getApiVersion() . '/', '', parse_url($url, PHP_URL_PATH)));

        return $response->successful() ? ($response->data()['raw'] ?? null) : null;
    }

    public function delete(string $mediaId): bool
    {
        $response = $this->client->delete($mediaId);

        return $response->successful();
    }
}
