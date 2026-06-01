<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\BusinessProfileService as BusinessProfileServiceContract;
use Aghfatehi\WhatsApp\Contracts\Client;

class BusinessProfileService implements BusinessProfileServiceContract
{
    public function __construct(private Client $client) {}

    public function get(string $phoneNumberId): array
    {
        $response = $this->client->get("{$phoneNumberId}/whatsapp_business_profile", [
            'fields' => 'id,about,address,description,email,profile_picture_url,websites,vertical',
        ]);

        return $response->get('data.0', []);
    }

    public function update(string $phoneNumberId, array $data): bool
    {
        $response = $this->client->post("{$phoneNumberId}/whatsapp_business_profile", [
            'messaging_product' => 'whatsapp',
        ] + $data);

        return $response->successful();
    }
}
