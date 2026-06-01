<?php

namespace Aghfatehi\WhatsApp\Contracts;

interface MessageService
{
    public function sendText(string $to, string $body, bool $previewUrl = false): array;

    public function sendMedia(string $to, string $type, string $mediaUrl, ?string $caption = null, ?string $filename = null): array;

    public function sendMediaById(string $to, string $type, string $mediaId, ?string $caption = null, ?string $filename = null): array;

    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en', array $components = []): array;

    public function sendInteractive(string $to, array $interactiveData): array;

    public function sendLocation(string $to, float $latitude, float $longitude, string $name = '', string $address = ''): array;

    public function sendContacts(string $to, array $contacts): array;

    public function sendReaction(string $to, string $messageId, string $emoji): array;

    public function markAsRead(string $messageId): array;
}
