<?php

namespace Aghfatehi\WhatsApp\Exceptions;

class AuthenticationException extends WhatsAppException
{
    public function __construct(string $message = 'Invalid or expired API token', array $context = [])
    {
        parent::__construct($message, 401, null, $context);
    }
}
