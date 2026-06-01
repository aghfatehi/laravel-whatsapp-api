<?php

namespace Aghfatehi\WhatsApp\Tests\Unit;

use Aghfatehi\WhatsApp\Exceptions\AuthenticationException;
use Aghfatehi\WhatsApp\Exceptions\RateLimitException;
use Aghfatehi\WhatsApp\Exceptions\ValidationException;
use Aghfatehi\WhatsApp\Exceptions\WhatsAppException;
use PHPUnit\Framework\TestCase;

class ExceptionsTest extends TestCase
{
    public function test_whatsapp_exception(): void
    {
        $e = new WhatsAppException('Error', 400, null, ['key' => 'value']);
        $this->assertEquals('Error', $e->getMessage());
        $this->assertEquals(400, $e->getCode());
        $this->assertEquals(['key' => 'value'], $e->getContext());
    }

    public function test_rate_limit_exception(): void
    {
        $e = new RateLimitException('Too fast', 10);
        $this->assertEquals(429, $e->getCode());
        $this->assertEquals(10, $e->getRetryAfter());
        $this->assertInstanceOf(WhatsAppException::class, $e);
    }

    public function test_authentication_exception(): void
    {
        $e = new AuthenticationException('Bad token');
        $this->assertEquals(401, $e->getCode());
        $this->assertInstanceOf(WhatsAppException::class, $e);
    }

    public function test_validation_exception(): void
    {
        $errors = ['phone' => ['required']];
        $e = new ValidationException('Invalid', $errors);
        $this->assertEquals(422, $e->getCode());
        $this->assertEquals($errors, $e->getErrors());
        $this->assertInstanceOf(WhatsAppException::class, $e);
    }
}
