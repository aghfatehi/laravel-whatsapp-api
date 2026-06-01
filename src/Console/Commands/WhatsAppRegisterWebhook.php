<?php

namespace Aghfatehi\WhatsApp\Console\Commands;

use Aghfatehi\WhatsApp\Facades\WhatsApp;
use Illuminate\Console\Command;

class WhatsAppRegisterWebhook extends Command
{
    protected $signature = 'whatsapp:register-webhook {url? : The webhook callback URL}';
    protected $description = 'Register or update the webhook subscription with Meta';

    public function handle(): int
    {
        $url = $this->argument('url') ?? $this->ask('Enter your webhook callback URL (e.g., https://example.com/api/whatsapp/webhook)');

        if (empty($url)) {
            $this->error('Webhook URL is required.');
            return Command::FAILURE;
        }

        $wabaId = config('whatsapp.connections.default.waba_id');
        if (empty($wabaId)) {
            $this->error('WHATSAPP_WABA_ID is not configured.');
            return Command::FAILURE;
        }

        $fields = 'messages,message_template_status_update,account_update,message_deliveries,message_reads';

        $this->info("Registering webhook URL: {$url}");
        $this->line("Fields: {$fields}");

        $this->warn('Note: This command requires a System User access token with sufficient permissions.');
        $this->warn('For production, register the webhook manually via the Meta Business Platform.');
        $this->newLine();
        $this->line('Webhook callback URL should point to:');
        $this->line('  GET  ' . url('/api/whatsapp/webhook') . ' (verification)');
        $this->line('  POST ' . url('/api/whatsapp/webhook') . ' (incoming messages)');

        return Command::SUCCESS;
    }
}
