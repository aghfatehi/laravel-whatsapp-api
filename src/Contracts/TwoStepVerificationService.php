<?php

namespace Aghfatehi\WhatsApp\Contracts;

interface TwoStepVerificationService
{
    public function setPin(string $phoneNumberId, string $pin): bool;

    public function deletePin(string $phoneNumberId): bool;
}
