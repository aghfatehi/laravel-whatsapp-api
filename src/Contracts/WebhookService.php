<?php

namespace Aghfatehi\WhatsApp\Contracts;

use Psr\Http\Message\ServerRequestInterface;

interface WebhookService
{
    public function verifyToken(string $mode, string $token, string $challenge): mixed;

    public function verifySignature(string $body, string $signature): bool;

    public function parsePayload(array $payload): array;

    public function handle(array $payload): void;
}
