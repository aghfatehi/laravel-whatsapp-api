<?php

namespace Aghfatehi\WhatsApp\Tests\Unit;

use Aghfatehi\WhatsApp\Exceptions\AuthenticationException;
use Aghfatehi\WhatsApp\Http\Client;
use Aghfatehi\WhatsApp\Http\ApiResponse;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    private array $config;

    protected function setUp(): void
    {
        $this->config = [
            'api_token' => 'test-token',
            'phone_number_id' => '123456',
            'waba_id' => 'waba-789',
            'api_version' => 'v22.0',
            'base_url' => 'https://graph.facebook.com',
            'timeout' => 5,
            'retry_on_throttle' => false,
            'max_retries' => 1,
        ];
    }

    public function test_can_instantiate_client(): void
    {
        $client = new Client($this->config);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function test_can_set_api_token(): void
    {
        $client = new Client($this->config);
        $result = $client->setApiToken('new-token');
        $this->assertInstanceOf(Client::class, $result);
    }

    public function test_can_set_phone_number_id(): void
    {
        $client = new Client($this->config);
        $result = $client->setPhoneNumberId('999');
        $this->assertInstanceOf(Client::class, $result);
        $this->assertEquals('999', $client->getPhoneNumberId());
    }

    public function test_returns_phone_number_id(): void
    {
        $client = new Client($this->config);
        $this->assertEquals('123456', $client->getPhoneNumberId());
    }

    public function test_returns_api_version(): void
    {
        $client = new Client($this->config);
        $this->assertEquals('v22.0', $client->getApiVersion());
    }

    public function test_returns_null_phone_number_when_not_set(): void
    {
        $client = new Client([]);
        $this->assertEmpty($client->getPhoneNumberId());
    }

    public function test_api_response_wrapper(): void
    {
        $response = new ApiResponse(['key' => 'value'], 200);
        $this->assertTrue($response->successful());
        $this->assertFalse($response->failed());
        $this->assertEquals('value', $response->get('key'));
        $this->assertFalse($response->isRateLimit());
        $this->assertFalse($response->isClientError());
        $this->assertFalse($response->isServerError());
    }

    public function test_api_response_client_error(): void
    {
        $response = new ApiResponse(['error' => ['message' => 'Bad Request']], 400);
        $this->assertFalse($response->successful());
        $this->assertTrue($response->failed());
        $this->assertTrue($response->isClientError());
        $this->assertFalse($response->isServerError());
    }

    public function test_api_response_rate_limit(): void
    {
        $response = new ApiResponse([], 429);
        $this->assertTrue($response->isRateLimit());
        $this->assertTrue($response->isClientError());
    }

    public function test_api_response_server_error(): void
    {
        $response = new ApiResponse([], 500);
        $this->assertTrue($response->isServerError());
        $this->assertFalse($response->isClientError());
    }
}
