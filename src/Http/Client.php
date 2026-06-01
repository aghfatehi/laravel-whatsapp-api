<?php

namespace Aghfatehi\WhatsApp\Http;

use Aghfatehi\WhatsApp\Contracts\Client as ClientContract;
use Aghfatehi\WhatsApp\Exceptions\AuthenticationException;
use Aghfatehi\WhatsApp\Exceptions\RateLimitException;
use Aghfatehi\WhatsApp\Exceptions\WhatsAppException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

class Client implements ClientContract
{
    private const MAX_RETRIES = 3;
    private const TIMEOUT = 10;

    private GuzzleClient $http;
    private string $apiToken;
    private string $phoneNumberId;
    private string $wabaId;
    private string $apiVersion;
    private string $baseUrl;
    private int $timeout;
    private bool $retryOnThrottle;
    private int $maxRetries;
    private ?LoggerInterface $logger;

    public function __construct(array $config = [], ?LoggerInterface $logger = null)
    {
        $this->apiToken = $config['api_token'] ?? '';
        $this->phoneNumberId = $config['phone_number_id'] ?? '';
        $this->wabaId = $config['waba_id'] ?? '';
        $this->apiVersion = $config['api_version'] ?? 'v22.0';
        $this->baseUrl = rtrim($config['base_url'] ?? 'https://graph.facebook.com', '/');
        $this->timeout = $config['timeout'] ?? self::TIMEOUT;
        $this->retryOnThrottle = $config['retry_on_throttle'] ?? true;
        $this->maxRetries = $config['max_retries'] ?? self::MAX_RETRIES;
        $this->logger = $logger;

        $this->http = new GuzzleClient([
            RequestOptions::TIMEOUT => $this->timeout,
            RequestOptions::CONNECT_TIMEOUT => $this->timeout,
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
        ]);
    }

    public function get(string $path, array $query = []): ApiResponse
    {
        return $this->send('GET', $path, ['query' => $query]);
    }

    public function post(string $path, array $data = []): ApiResponse
    {
        return $this->send('POST', $path, [
            RequestOptions::JSON => $data,
        ]);
    }

    public function postForm(string $path, array $data = [], array $files = []): ApiResponse
    {
        $multipart = [];

        foreach ($data as $key => $value) {
            $multipart[] = ['name' => $key, 'contents' => $value];
        }

        foreach ($files as $key => $file) {
            $multipart[] = [
                'name' => $key,
                'contents' => fopen($file['path'] ?? $file, 'r'),
                'filename' => $file['filename'] ?? basename($file['path'] ?? $file),
            ];
        }

        return $this->send('POST', $path, [
            RequestOptions::MULTIPART => $multipart,
        ]);
    }

    public function delete(string $path): ApiResponse
    {
        return $this->send('DELETE', $path);
    }

    public function setApiToken(string $token): static
    {
        $this->apiToken = $token;
        return $this;
    }

    public function setPhoneNumberId(string $phoneNumberId): static
    {
        $this->phoneNumberId = $phoneNumberId;
        return $this;
    }

    public function getPhoneNumberId(): ?string
    {
        return $this->phoneNumberId;
    }

    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    public function getWabaId(): ?string
    {
        return $this->wabaId;
    }

    private function send(string $method, string $path, array $options = []): ApiResponse
    {
        $uri = "{$this->baseUrl}/{$this->apiVersion}/{$path}";

        if (!isset($options[RequestOptions::HEADERS])) {
            $options[RequestOptions::HEADERS] = [];
        }

        $options[RequestOptions::HEADERS]['Authorization'] = "Bearer {$this->apiToken}";

        $attempt = 0;

        do {
            $attempt++;

            try {
                $startTime = microtime(true);
                $response = $this->http->request($method, $uri, $options);
                $latency = (microtime(true) - $startTime) * 1000;

                $body = (string) $response->getBody();
                $statusCode = $response->getStatusCode();
                $data = json_decode($body, true) ?? ['raw' => $body];
                $headers = $response->getHeaders();

                $apiResponse = new ApiResponse($data, $statusCode, $headers);

                if ($this->logger) {
                    $this->logger->info("WhatsApp API: {$method} {$path} -> {$statusCode}", [
                        'method' => $method,
                        'path' => $path,
                        'status' => $statusCode,
                        'latency_ms' => round($latency, 1),
                        'attempt' => $attempt,
                    ]);
                }

                if ($apiResponse->isRateLimit() && $this->retryOnThrottle && $attempt < $this->maxRetries) {
                    $retryAfter = (int) ($response->getHeaderLine('Retry-After') ?: 2 ** $attempt);
                    if ($this->logger) {
                        $this->logger->warning("WhatsApp API rate limited, retrying in {$retryAfter}s", [
                            'attempt' => $attempt,
                            'retry_after' => $retryAfter,
                        ]);
                    }
                    sleep($retryAfter);
                    continue;
                }

                if ($apiResponse->isClientError()) {
                    $this->handleClientError($apiResponse, $path, $data);
                }

                return $apiResponse;

            } catch (ConnectException $e) {
                if ($attempt < $this->maxRetries) {
                    $backoff = 2 ** $attempt;
                    if ($this->logger) {
                        $this->logger->warning("WhatsApp API connection failed, retrying in {$backoff}s", [
                            'attempt' => $attempt,
                            'error' => $e->getMessage(),
                        ]);
                    }
                    sleep($backoff);
                    continue;
                }

                throw new WhatsAppException("Connection failed after {$this->maxRetries} attempts: " . $e->getMessage(), 0, $e);
            }
        } while ($attempt < $this->maxRetries);

        throw new WhatsAppException("Request failed after {$this->maxRetries} attempts");
    }

    private function handleClientError(ApiResponse $response, string $path, array $data): void
    {
        $code = $response->statusCode();
        $error = $data['error'] ?? [];
        $message = $error['message'] ?? 'Unknown error';
        $context = ['path' => $path, 'response' => $data];

        throw match ($code) {
            401 => new AuthenticationException($message, $context),
            429 => new RateLimitException($message, (int) ($error['error_data']['retry_after'] ?? 5), $context),
            default => new WhatsAppException($message, $code, null, $context),
        };
    }
}
