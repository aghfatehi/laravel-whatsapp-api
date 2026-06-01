<?php

namespace Aghfatehi\WhatsApp\Contracts;

interface TemplateService
{
    public function list(string $status = 'APPROVED', int $limit = 50): array;

    public function get(string $templateId): array;

    public function create(array $data): array;

    public function update(string $templateId, array $data): bool;

    public function delete(string $templateId): bool;
}
