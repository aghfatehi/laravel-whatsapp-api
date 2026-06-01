<?php

namespace Aghfatehi\WhatsApp\Console\Commands;

use Aghfatehi\WhatsApp\Facades\WhatsApp;
use Illuminate\Console\Command;

class WhatsAppListPhoneNumbers extends Command
{
    protected $signature = 'whatsapp:phone-numbers';
    protected $description = 'List phone numbers associated with the WhatsApp Business Account';

    public function handle(): int
    {
        $this->info('Fetching phone numbers from WhatsApp Business Account...');

        try {
            $numbers = WhatsApp::phoneNumbers()->list();

            if (empty($numbers)) {
                $this->warn('No phone numbers found. Ensure WHATSAPP_WABA_ID is correctly set.');
                return Command::FAILURE;
            }

            $rows = array_map(fn ($n) => [
                $n['id'] ?? 'N/A',
                $n['display_phone_number'] ?? 'N/A',
                $n['verified_name'] ?? 'N/A',
                $n['quality_rating'] ?? 'N/A',
                $n['status'] ?? 'N/A',
            ], $numbers);

            $this->table(['ID', 'Phone Number', 'Verified Name', 'Quality', 'Status'], $rows);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Failed to fetch phone numbers: ' . $e->getMessage());
            $this->line('Check your WHATSAPP_API_TOKEN and WHATSAPP_WABA_ID configuration.');

            return Command::FAILURE;
        }
    }
}
