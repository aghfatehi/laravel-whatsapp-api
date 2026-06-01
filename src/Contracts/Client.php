<?php

namespace Aghfatehi\WhatsApp\Contracts;

use Aghfatehi\WhatsApp\Http\ApiResponse;

interface Client
{
    public function get(string $path, array $query = []): ApiResponse;

    public function post(string $path, array $data = []): ApiResponse;

    public function postForm(string $path, array $data = [], array $files = []): ApiResponse;

    public function delete(string $path): ApiResponse;

    public function setApiToken(string $token): static;

    public function setPhoneNumberId(string $phoneNumberId): static;

    public function getPhoneNumberId(): ?string;

    public function getApiVersion(): string;
}
