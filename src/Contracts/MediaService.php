<?php

namespace Aghfatehi\WhatsApp\Contracts;

interface MediaService
{
    public function upload(string $filePath, string $type): array;

    public function getUrl(string $mediaId): ?string;

    public function download(string $mediaId): ?string;

    public function delete(string $mediaId): bool;
}
