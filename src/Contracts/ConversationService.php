<?php

namespace Aghfatehi\WhatsApp\Contracts;

interface ConversationService
{
    public function list(string $phoneNumberId, array $filters = []): array;

    public function getAnalytics(string $phoneNumberId, array $dimensions = []): array;

    public function getPricing(string $phoneNumberId): array;
}
