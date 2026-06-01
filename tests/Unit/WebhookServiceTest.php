<?php

namespace Aghfatehi\WhatsApp\Tests\Unit;

use Aghfatehi\WhatsApp\Services\WebhookService;
use PHPUnit\Framework\TestCase;

class WebhookServiceTest extends TestCase
{
    private WebhookService $service;

    protected function setUp(): void
    {
        $this->service = new WebhookService([
            'app_secret' => 'my-secret',
            'verify_token' => 'my-token',
            'webhook' => [
                'verify_token' => 'my-token',
            ],
        ]);
    }

    public function test_verify_token_success(): void
    {
        $result = $this->service->verifyToken('subscribe', 'my-token', 'challenge123');
        $this->assertEquals('challenge123', $result);
    }

    public function test_verify_token_fails_with_wrong_token(): void
    {
        $result = $this->service->verifyToken('subscribe', 'wrong-token', 'challenge123');
        $this->assertFalse($result);
    }

    public function test_verify_token_fails_with_wrong_mode(): void
    {
        $result = $this->service->verifyToken('unsubscribe', 'my-token', 'challenge123');
        $this->assertFalse($result);
    }

    public function test_verify_signature_success(): void
    {
        $body = '{"test": true}';
        $expected = 'sha256=' . hash_hmac('sha256', $body, 'my-secret');
        $this->assertTrue($this->service->verifySignature($body, $expected));
    }

    public function test_verify_signature_fails(): void
    {
        $body = '{"test": true}';
        $this->assertFalse($this->service->verifySignature($body, 'sha256=invalid'));
    }

    public function test_verify_signature_fails_with_empty_secret(): void
    {
        $service = new WebhookService([]);
        $this->assertFalse($service->verifySignature('body', 'signature'));
    }

    public function test_parse_text_message(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => '123',
                'changes' => [[
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => [
                            'phone_number_id' => '456',
                            'display_phone_number' => '15550000000',
                        ],
                        'contacts' => [[
                            'profile' => ['name' => 'John'],
                            'wa_id' => '966555555555',
                        ]],
                        'messages' => [[
                            'from' => '966555555555',
                            'id' => 'wamid.123',
                            'timestamp' => '1700000000',
                            'type' => 'text',
                            'text' => ['body' => 'Hello there'],
                        ]],
                    ],
                ]],
            ]],
        ];

        $result = $this->service->parsePayload($payload);

        $this->assertEquals('whatsapp_business_account', $result['object']);
        $this->assertCount(1, $result['messages']);
        $this->assertEquals('966555555555', $result['messages'][0]['from']);
        $this->assertEquals('John', $result['messages'][0]['from_name']);
        $this->assertEquals('text', $result['messages'][0]['type']);
        $this->assertEquals('Hello there', $result['messages'][0]['content']['body']);
    }

    public function test_parse_image_message(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => '123',
                'changes' => [[
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => ['phone_number_id' => '456', 'display_phone_number' => '15550000000'],
                        'contacts' => [],
                        'messages' => [[
                            'from' => '966555555555',
                            'id' => 'wamid.456',
                            'timestamp' => '1700000000',
                            'type' => 'image',
                            'image' => [
                                'id' => 'media.789',
                                'mime_type' => 'image/jpeg',
                                'sha256' => 'abc123',
                                'caption' => 'Nice!',
                            ],
                        ]],
                    ],
                ]],
            ]],
        ];

        $result = $this->service->parsePayload($payload);

        $this->assertCount(1, $result['messages']);
        $this->assertEquals('image', $result['messages'][0]['type']);
        $this->assertEquals('media.789', $result['messages'][0]['content']['id']);
        $this->assertEquals('image/jpeg', $result['messages'][0]['content']['mime_type']);
        $this->assertEquals('Nice!', $result['messages'][0]['content']['caption']);
    }

    public function test_parse_status_update(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [[
                'id' => '123',
                'changes' => [[
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata' => ['phone_number_id' => '456', 'display_phone_number' => '15550000000'],
                        'statuses' => [[
                            'id' => 'status.1',
                            'message_id' => 'wamid.999',
                            'status' => 'sent',
                            'timestamp' => '1700000001',
                            'recipient_id' => '966555555555',
                        ]],
                    ],
                ]],
            ]],
        ];

        $result = $this->service->parsePayload($payload);

        $this->assertCount(1, $result['statuses']);
        $this->assertEquals('sent', $result['statuses'][0]['status']);
        $this->assertEquals('wamid.999', $result['statuses'][0]['message_id']);
    }
}
