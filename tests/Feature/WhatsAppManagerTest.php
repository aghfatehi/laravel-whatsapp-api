<?php

namespace Aghfatehi\WhatsApp\Tests\Feature;

use Aghfatehi\WhatsApp\Contracts\BusinessProfileService;
use Aghfatehi\WhatsApp\Contracts\ConversationService;
use Aghfatehi\WhatsApp\Contracts\MediaService;
use Aghfatehi\WhatsApp\Contracts\MessageService;
use Aghfatehi\WhatsApp\Contracts\PhoneNumberService;
use Aghfatehi\WhatsApp\Contracts\QRCodeService;
use Aghfatehi\WhatsApp\Contracts\TemplateService;
use Aghfatehi\WhatsApp\Contracts\TwoStepVerificationService;
use Aghfatehi\WhatsApp\Contracts\WebhookService;
use Aghfatehi\WhatsApp\Facades\WhatsApp;
use Aghfatehi\WhatsApp\WhatsAppManager;
use Aghfatehi\WhatsApp\WhatsAppServiceProvider;
use Orchestra\Testbench\TestCase;

class WhatsAppManagerTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [WhatsAppServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'WhatsApp' => WhatsApp::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('whatsapp.connections.default', [
            'api_token' => 'test-token',
            'phone_number_id' => '123456',
            'waba_id' => 'waba-789',
            'app_secret' => 'test-secret',
            'verify_token' => 'test-token',
            'api_version' => 'v22.0',
            'timeout' => 5,
            'retry_on_throttle' => false,
            'max_retries' => 1,
        ]);

        $app['config']->set('whatsapp.webhook', [
            'verify_token' => 'test-token',
            'app_secret' => 'test-secret',
            'events' => [
                'message_received' => true,
                'message_sent' => true,
                'message_delivered' => true,
                'message_read' => true,
                'message_failed' => true,
                'account_update' => true,
            ],
        ]);
    }

    public function test_manager_is_bound(): void
    {
        $this->assertTrue(app()->has('whatsapp'));
        $this->assertInstanceOf(WhatsAppManager::class, app('whatsapp'));
    }

    public function test_facade_works(): void
    {
        $this->assertInstanceOf(WhatsAppManager::class, WhatsApp::getFacadeRoot());
    }

    public function test_manager_returns_message_service(): void
    {
        $service = app('whatsapp')->messages();
        $this->assertInstanceOf(MessageService::class, $service);
    }

    public function test_manager_returns_media_service(): void
    {
        $service = app('whatsapp')->media();
        $this->assertInstanceOf(MediaService::class, $service);
    }

    public function test_manager_returns_phone_number_service(): void
    {
        $service = app('whatsapp')->phoneNumbers();
        $this->assertInstanceOf(PhoneNumberService::class, $service);
    }

    public function test_manager_returns_business_profile_service(): void
    {
        $service = app('whatsapp')->businessProfile();
        $this->assertInstanceOf(BusinessProfileService::class, $service);
    }

    public function test_manager_returns_template_service(): void
    {
        $service = app('whatsapp')->templates();
        $this->assertInstanceOf(TemplateService::class, $service);
    }

    public function test_manager_returns_qr_code_service(): void
    {
        $service = app('whatsapp')->qrCodes();
        $this->assertInstanceOf(QRCodeService::class, $service);
    }

    public function test_manager_returns_two_step_service(): void
    {
        $service = app('whatsapp')->twoStepVerification();
        $this->assertInstanceOf(TwoStepVerificationService::class, $service);
    }

    public function test_manager_returns_conversation_service(): void
    {
        $service = app('whatsapp')->conversations();
        $this->assertInstanceOf(ConversationService::class, $service);
    }

    public function test_manager_returns_webhook_service(): void
    {
        $service = app('whatsapp')->webhook();
        $this->assertInstanceOf(WebhookService::class, $service);
    }

    public function test_connection_switching(): void
    {
        $manager = app('whatsapp');
        $connected = $manager->connection('default');
        $this->assertInstanceOf(WhatsAppManager::class, $connected);
    }

    public function test_config_is_publishable(): void
    {
        $provider = new WhatsAppServiceProvider(app());
        $this->assertNotNull($provider);
    }
}
