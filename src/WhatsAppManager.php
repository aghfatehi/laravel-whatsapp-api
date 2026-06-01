<?php

namespace Aghfatehi\WhatsApp;

use Aghfatehi\WhatsApp\Contracts\BusinessProfileService;
use Aghfatehi\WhatsApp\Contracts\ConversationService;
use Aghfatehi\WhatsApp\Contracts\MediaService;
use Aghfatehi\WhatsApp\Contracts\MessageService;
use Aghfatehi\WhatsApp\Contracts\PhoneNumberService;
use Aghfatehi\WhatsApp\Contracts\QRCodeService;
use Aghfatehi\WhatsApp\Contracts\TemplateService;
use Aghfatehi\WhatsApp\Contracts\TwoStepVerificationService;
use Aghfatehi\WhatsApp\Contracts\WebhookService;
use Aghfatehi\WhatsApp\Http\Client;
use Aghfatehi\WhatsApp\Services\BusinessProfileService as BusinessProfile;
use Aghfatehi\WhatsApp\Services\ConversationService as Conversations;
use Aghfatehi\WhatsApp\Services\MediaService as Media;
use Aghfatehi\WhatsApp\Services\MessageService as Messages;
use Aghfatehi\WhatsApp\Services\PhoneNumberService as PhoneNumbers;
use Aghfatehi\WhatsApp\Services\QRCodeService as QRCodes;
use Aghfatehi\WhatsApp\Services\TemplateService as Templates;
use Aghfatehi\WhatsApp\Services\TwoStepVerificationService as TwoStep;
use Aghfatehi\WhatsApp\Services\WebhookService as Webhook;
use Psr\Log\LoggerInterface;

class WhatsAppManager
{
    private array $connections = [];
    private array $services = [];
    private ?LoggerInterface $logger;

    public function __construct(private array $config, ?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function connection(?string $name = null): static
    {
        $name = $name ?? ($this->config['default'] ?? 'default');

        if (!isset($this->connections[$name])) {
            $connConfig = $this->config['connections'][$name] ?? throw new \InvalidArgumentException("WhatsApp connection [{$name}] not configured.");
            $this->connections[$name] = new Client($connConfig, $this->logger);
        }

        $manager = clone $this;
        $manager->services = [];
        $manager->connections = [$name => $this->connections[$name]];
        $manager->config = $this->config;

        return $manager;
    }

    public function messages(): MessageService
    {
        return $this->service('messages', fn () => new Messages($this->client()));
    }

    public function media(): MediaService
    {
        return $this->service('media', fn () => new Media($this->client()));
    }

    public function phoneNumbers(): PhoneNumberService
    {
        return $this->service('phone_numbers', fn () => new PhoneNumbers($this->client()));
    }

    public function businessProfile(): BusinessProfileService
    {
        return $this->service('business_profile', fn () => new BusinessProfile($this->client()));
    }

    public function templates(): TemplateService
    {
        return $this->service('templates', fn () => new Templates($this->client()));
    }

    public function qrCodes(): QRCodeService
    {
        return $this->service('qr_codes', fn () => new QRCodes($this->client()));
    }

    public function twoStepVerification(): TwoStepVerificationService
    {
        return $this->service('two_step', fn () => new TwoStep($this->client()));
    }

    public function conversations(): ConversationService
    {
        return $this->service('conversations', fn () => new Conversations($this->client()));
    }

    public function webhook(): WebhookService
    {
        return $this->service('webhook', fn () => new Webhook(
            $this->currentConnectionConfig(),
            $this->logger
        ));
    }

    public function getClient(): Client
    {
        return $this->client();
    }

    private function client(): Client
    {
        if (empty($this->connections)) {
            $this->connection();
        }
        $name = key($this->connections);
        return $this->connections[$name];
    }

    private function service(string $key, callable $factory): mixed
    {
        if (!isset($this->services[$key])) {
            $this->services[$key] = $factory();
        }

        return $this->services[$key];
    }

    private function currentConnectionConfig(): array
    {
        $name = key($this->connections) ?? $this->config['default'] ?? 'default';
        return $this->config['connections'][$name] ?? [];
    }
}
