<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\Client;
use Aghfatehi\WhatsApp\Contracts\ConversationService as ConversationServiceContract;

class ConversationService implements ConversationServiceContract
{
    public function __construct(private Client $client) {}

    public function list(string $phoneNumberId, array $filters = []): array
    {
        $params = array_merge([
            'fields' => 'id,conversation_id,expiration_timestamp,origin_type,phone_number_id',
        ], $filters);

        $response = $this->client->get("{$phoneNumberId}/conversations", $params);

        return $response->get('data', []);
    }

    public function getAnalytics(string $phoneNumberId, array $dimensions = []): array
    {
        $wabaId = $this->client->getWabaId();
        $response = $this->client->get("{$wabaId}/analytics", array_merge([
            'phone_numbers' => $phoneNumberId,
            'dimensions' => implode(',', $dimensions ?: ['country', 'conversation_type']),
        ]));

        return $response->get('data', []);
    }

    public function getPricing(string $phoneNumberId): array
    {
        $wabaId = $this->client->getWabaId();
        $response = $this->client->get("{$wabaId}/pricing", [
            'phone_numbers' => $phoneNumberId,
        ]);

        return $response->data();
    }
}
