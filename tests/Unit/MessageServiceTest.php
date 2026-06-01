<?php

namespace Aghfatehi\WhatsApp\Tests\Unit;

use Aghfatehi\WhatsApp\Http\ApiResponse;
use Aghfatehi\WhatsApp\Http\Client;
use Aghfatehi\WhatsApp\Services\MessageService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MessageServiceTest extends TestCase
{
    private Client&MockObject $client;
    private MessageService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->client->method('getPhoneNumberId')->willReturn('123456');
        $this->service = new MessageService($this->client);
    }

    public function test_send_text_message(): void
    {
        $expectedPayload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => '966555555555',
            'type' => 'text',
            'text' => ['preview_url' => false, 'body' => 'Hello!'],
        ];

        $this->client->expects($this->once())
            ->method('post')
            ->with('123456/messages', $expectedPayload)
            ->willReturn(new ApiResponse(['messages' => [['id' => 'msg1']]], 200));

        $result = $this->service->sendText('966555555555', 'Hello!');

        $this->assertArrayHasKey('messages', $result);
        $this->assertEquals('msg1', $result['messages'][0]['id']);
    }

    public function test_send_media_message(): void
    {
        $expectedPayload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => '966555555555',
            'type' => 'image',
            'image' => ['link' => 'https://example.com/image.jpg', 'caption' => 'Photo'],
        ];

        $this->client->expects($this->once())
            ->method('post')
            ->with('123456/messages', $expectedPayload)
            ->willReturn(new ApiResponse(['messages' => [['id' => 'msg2']]], 200));

        $result = $this->service->sendMedia('966555555555', 'image', 'https://example.com/image.jpg', 'Photo');

        $this->assertArrayHasKey('messages', $result);
    }

    public function test_send_template_message(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with($this->equalTo('123456/messages'), $this->callback(function ($payload) {
                return $payload['type'] === 'template'
                    && $payload['template']['name'] === 'hello_world'
                    && $payload['template']['language']['code'] === 'en';
            }))
            ->willReturn(new ApiResponse(['messages' => [['id' => 'msg3']]], 200));

        $result = $this->service->sendTemplate('966555555555', 'hello_world');

        $this->assertArrayHasKey('messages', $result);
    }

    public function test_mark_as_read(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('123456/messages', [
                'messaging_product' => 'whatsapp',
                'status' => 'read',
                'message_id' => 'msg_123',
            ])
            ->willReturn(new ApiResponse([], 200));

        $result = $this->service->markAsRead('msg_123');

        $this->assertIsArray($result);
    }

    public function test_send_reaction(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('123456/messages', $this->callback(function ($p) {
                return $p['type'] === 'reaction'
                    && $p['reaction']['message_id'] === 'msg_123'
                    && $p['reaction']['emoji'] === '👍';
            }))
            ->willReturn(new ApiResponse([], 200));

        $result = $this->service->sendReaction('966555555555', 'msg_123', '👍');

        $this->assertIsArray($result);
    }

    public function test_send_location(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('123456/messages', $this->callback(function ($p) {
                return $p['type'] === 'location'
                    && $p['location']['latitude'] === 24.7136
                    && $p['location']['longitude'] === 46.6753;
            }))
            ->willReturn(new ApiResponse([], 200));

        $result = $this->service->sendLocation('966555555555', 24.7136, 46.6753, 'Riyadh');

        $this->assertIsArray($result);
    }
}
