<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\Client;
use Aghfatehi\WhatsApp\Contracts\TwoStepVerificationService as TwoStepVerificationServiceContract;

class TwoStepVerificationService implements TwoStepVerificationServiceContract
{
    public function __construct(private Client $client) {}

    public function setPin(string $phoneNumberId, string $pin): bool
    {
        $response = $this->client->post("{$phoneNumberId}/two_step_verification", [
            'pin' => $pin,
            'messaging_product' => 'whatsapp',
        ]);

        return $response->successful();
    }

    public function deletePin(string $phoneNumberId): bool
    {
        $response = $this->client->delete("{$phoneNumberId}/two_step_verification");

        return $response->successful();
    }
}
