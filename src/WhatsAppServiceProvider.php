<?php

namespace Aghfatehi\WhatsApp;

use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class WhatsAppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/whatsapp.php', 'whatsapp');

        $this->app->singleton('whatsapp', function ($app) {
            $config = $app->config->get('whatsapp', []);
            $logger = $app->bound(LoggerInterface::class) ? $app->make(LoggerInterface::class) : null;

            return new WhatsAppManager($config, $logger);
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/whatsapp.php' => config_path('whatsapp.php'),
            ], 'whatsapp-config');

            $this->commands([
                Console\Commands\WhatsAppSetupCommand::class,
                Console\Commands\WhatsAppListPhoneNumbers::class,
                Console\Commands\WhatsAppRegisterWebhook::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__ . '/Routes/webhook.php');
    }
}
