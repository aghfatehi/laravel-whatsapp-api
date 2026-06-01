<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\Client;
use Aghfatehi\WhatsApp\Contracts\PhoneNumberService as PhoneNumberServiceContract;

class PhoneNumberService implements PhoneNumberServiceContract
{
    public function __construct(private Client $client) {}

    public function list(): array
    {
        $wabaId = $this->client->getWabaId();
        $response = $this->client->get("{$wabaId}/phone_numbers", [
            'fields' => 'id,display_phone_number,verified_name,quality_rating,code_verified,status',
        ]);

        return $response->get('data', []);
    }

    public function get(string $phoneNumberId): array
    {
        $response = $this->client->get($phoneNumberId, [
            'fields' => 'id,display_phone_number,verified_name,quality_rating,code_verified,status,name_status,new_name_status',
        ]);

        return $response->data();
    }

    public function requestPin(string $phoneNumberId): bool
    {
        $response = $this->client->post("{$phoneNumberId}/request_pin", [
            'messaging_product' => 'whatsapp',
        ]);

        return $response->successful();
    }

    public function verifyPin(string $phoneNumberId, string $pin): bool
    {
        $response = $this->client->post("{$phoneNumberId}/register", [
            'messaging_product' => 'whatsapp',
            'pin' => $pin,
        ]);

        return $response->successful();
    }

    public function deregister(string $phoneNumberId): bool
    {
        $response = $this->client->post("{$phoneNumberId}/deregister", [
            'messaging_product' => 'whatsapp',
        ]);

        return $response->successful();
    }
}
