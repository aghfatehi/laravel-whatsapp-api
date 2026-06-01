<?php

namespace Aghfatehi\WhatsApp\Tests\Unit;

use Aghfatehi\WhatsApp\Http\ApiResponse;
use Aghfatehi\WhatsApp\Http\Client;
use Aghfatehi\WhatsApp\Services\MediaService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaServiceTest extends TestCase
{
    private Client&MockObject $client;
    private MediaService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->client->method('getPhoneNumberId')->willReturn('123456');
        $this->service = new MediaService($this->client);
    }

    public function test_get_media_url(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('media-id-123')
            ->willReturn(new ApiResponse(['url' => 'https://graph.facebook.com/v22.0/download/abc'], 200));

        $url = $this->service->getUrl('media-id-123');

        $this->assertEquals('https://graph.facebook.com/v22.0/download/abc', $url);
    }

    public function test_get_media_url_returns_null_on_failure(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('invalid-media')
            ->willReturn(new ApiResponse(['error' => ['message' => 'Not found']], 404));

        $url = $this->service->getUrl('invalid-media');

        $this->assertNull($url);
    }

    public function test_delete_media(): void
    {
        $this->client->expects($this->once())
            ->method('delete')
            ->with('media-id-123')
            ->willReturn(new ApiResponse(['success' => true], 200));

        $result = $this->service->delete('media-id-123');

        $this->assertTrue($result);
    }
}
