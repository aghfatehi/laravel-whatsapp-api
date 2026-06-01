<?php

namespace Aghfatehi\WhatsApp\Contracts;

interface PhoneNumberService
{
    public function list(): array;

    public function get(string $phoneNumberId): array;

    public function requestPin(string $phoneNumberId): bool;

    public function verifyPin(string $phoneNumberId, string $pin): bool;

    public function deregister(string $phoneNumberId): bool;
}
