<?php

namespace Aghfatehi\WhatsApp\Services;

use Aghfatehi\WhatsApp\Contracts\Client;
use Aghfatehi\WhatsApp\Contracts\QRCodeService as QRCodeServiceContract;

class QRCodeService implements QRCodeServiceContract
{
    public function __construct(private Client $client) {}

    public function create(string $prefilledMessage = '', string $generateQrCode = 'WELCOME_MSG'): array
    {
        $response = $this->client->post($this->endpoint(), array_filter([
            'prefilled_message' => $prefilledMessage,
            'generate_qr_code' => $generateQrCode,
        ]));

        return $response->data();
    }

    public function list(): array
    {
        $response = $this->client->get($this->endpoint(), [
            'fields' => 'id,prefilled_message,qr_image_url,status,created_at',
        ]);

        return $response->get('data', []);
    }

    public function get(string $qrCodeId): array
    {
        $response = $this->client->get($qrCodeId, [
            'fields' => 'id,prefilled_message,qr_image_url,status,created_at',
        ]);

        return $response->data();
    }

    public function update(string $qrCodeId, array $data): bool
    {
        $response = $this->client->post($qrCodeId, $data);

        return $response->successful();
    }

    public function delete(string $qrCodeId): bool
    {
        $response = $this->client->delete($qrCodeId);

        return $response->successful();
    }

    private function endpoint(): string
    {
        return $this->client->getPhoneNumberId() . '/whatsapp_qr_codes';
    }
}
