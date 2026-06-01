<?php

namespace Aghfatehi\WhatsApp\Contracts;

interface QRCodeService
{
    public function create(string $prefilledMessage = '', string $generateQrCode = 'WELCOME_MSG'): array;

    public function list(): array;

    public function get(string $qrCodeId): array;

    public function update(string $qrCodeId, array $data): bool;

    public function delete(string $qrCodeId): bool;
}
