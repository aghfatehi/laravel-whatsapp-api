<?php

namespace Aghfatehi\WhatsApp\Exceptions;

class ValidationException extends WhatsAppException
{
    private array $errors;

    public function __construct(string $message = 'Validation failed', array $errors = [], array $context = [])
    {
        parent::__construct($message, 422, null, $context);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
