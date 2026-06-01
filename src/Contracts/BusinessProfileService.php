<?php

namespace Aghfatehi\WhatsApp\Contracts;

interface BusinessProfileService
{
    public function get(string $phoneNumberId): array;

    public function update(string $phoneNumberId, array $data): bool;
}
