<?php

namespace Aghfatehi\WhatsApp\Exceptions;

class RateLimitException extends WhatsAppException
{
    private int $retryAfter;

    public function __construct(string $message = 'Rate limit exceeded', int $retryAfter = 5, array $context = [])
    {
        parent::__construct($message, 429, null, $context);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): int
    {
        return $this->retryAfter;
    }
}
