<?php

namespace Aghfatehi\WhatsApp\Tests\Unit;

use Aghfatehi\WhatsApp\Http\ApiResponse;
use Aghfatehi\WhatsApp\Http\Client;
use Aghfatehi\WhatsApp\Services\PhoneNumberService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PhoneNumberServiceTest extends TestCase
{
    private Client&MockObject $client;
    private PhoneNumberService $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->client->method('getWabaId')->willReturn('waba-789');
        $this->service = new PhoneNumberService($this->client);
    }

    public function test_list_phone_numbers(): void
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with('waba-789/phone_numbers', $this->isType('array'))
            ->willReturn(new ApiResponse([
                'data' => [
                    ['id' => '123', 'display_phone_number' => '15550000000', 'status' => 'CONNECTED'],
                    ['id' => '456', 'display_phone_number' => '15551111111', 'status' => 'PENDING'],
                ],
            ], 200));

        $numbers = $this->service->list();

        $this->assertCount(2, $numbers);
        $this->assertEquals('15550000000', $numbers[0]['display_phone_number']);
    }

    public function test_request_pin(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('123/request_pin', ['messaging_product' => 'whatsapp'])
            ->willReturn(new ApiResponse(['success' => true], 200));

        $this->assertTrue($this->service->requestPin('123'));
    }

    public function test_verify_pin(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('123/register', ['messaging_product' => 'whatsapp', 'pin' => '123456'])
            ->willReturn(new ApiResponse(['success' => true], 200));

        $this->assertTrue($this->service->verifyPin('123', '123456'));
    }

    public function test_deregister(): void
    {
        $this->client->expects($this->once())
            ->method('post')
            ->with('123/deregister', ['messaging_product' => 'whatsapp'])
            ->willReturn(new ApiResponse(['success' => true], 200));

        $this->assertTrue($this->service->deregister('123'));
    }
}
