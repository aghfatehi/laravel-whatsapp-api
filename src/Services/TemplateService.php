<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\Client;
use Aghfatehi\WhatsApp\Contracts\TemplateService as TemplateServiceContract;

class TemplateService implements TemplateServiceContract
{
    public function __construct(private Client $client) {}

    public function list(string $status = 'APPROVED', int $limit = 50): array
    {
        $wabaId = $this->client->getWabaId();
        $response = $this->client->get("{$wabaId}/message_templates", [
            'status' => $status,
            'limit' => $limit,
            'fields' => 'id,name,language,status,category,components',
        ]);

        return $response->get('data', []);
    }

    public function get(string $templateId): array
    {
        $response = $this->client->get($templateId, [
            'fields' => 'id,name,language,status,category,components',
        ]);

        return $response->data();
    }

    public function create(array $data): array
    {
        $wabaId = $this->client->getWabaId();
        $response = $this->client->post("{$wabaId}/message_templates", $data);

        return $response->data();
    }

    public function update(string $templateId, array $data): bool
    {
        $response = $this->client->post($templateId, $data);

        return $response->successful();
    }

    public function delete(string $templateId): bool
    {
        $response = $this->client->delete($templateId);

        return $response->successful();
    }
}
