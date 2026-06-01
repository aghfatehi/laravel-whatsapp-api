<?php

namespace Aghfatehi\WhatsApp\Console\Commands;

use Illuminate\Console\Command;

class WhatsAppSetupCommand extends Command
{
    protected $signature = 'whatsapp:setup';
    protected $description = 'Validate WhatsApp Cloud API configuration and test connectivity';

    public function handle(): int
    {
        $this->info('Checking WhatsApp configuration...');

        $token = config('whatsapp.connections.default.api_token');
        $phoneNumberId = config('whatsapp.connections.default.phone_number_id');
        $wabaId = config('whatsapp.connections.default.waba_id');

        if (empty($token)) {
            $this->error('WHATSAPP_API_TOKEN is not set in .env');
            return Command::FAILURE;
        }

        if (empty($phoneNumberId)) {
            $this->warn('WHATSAPP_PHONE_NUMBER_ID is not set. Some features will be unavailable.');
        }

        if (empty($wabaId)) {
            $this->warn('WHATSAPP_WABA_ID is not set. Some features will be unavailable.');
        }

        $this->info('Configuration looks valid.');
        $this->newLine();
        $this->table(['Key', 'Status'], [
            ['API Token', strlen($token) > 10 ? '✓ Set' : '✗ Too short'],
            ['Phone Number ID', $phoneNumberId ? '✓ Set' : '○ Optional'],
            ['WABA ID', $wabaId ? '✓ Set' : '○ Optional'],
            ['App Secret', config('whatsapp.connections.default.app_secret') ? '✓ Set' : '○ Optional'],
            ['Verify Token', config('whatsapp.connections.default.verify_token') ? '✓ Set' : '○ Optional'],
        ]);

        $this->newLine();
        $this->info('Run php artisan whatsapp:phone-numbers to test API connectivity.');

        return Command::SUCCESS;
    }
}
