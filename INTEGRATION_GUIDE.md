# Integration Guide: PrintCommand × aghfatehi/laravel-whatsapp-api

## Overview

This guide explains how to integrate the `aghfatehi/laravel-whatsapp-api` package into the **PrintCommand** project, replacing the existing inline `WhatsAppService` with the standardized package.

## Step 1: Add Repository to PrintCommand

Edit `E:\fsoft_new_projects\PrintCommand\composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "F:/projects/aghfatehi/laravel-whatsapp-api"
        }
    ],
    "require": {
        "aghfatehi/laravel-whatsapp-api": "*"
    }
}
```

Then run:
```bash
cd E:\fsoft_new_projects\PrintCommand
composer update aghfatehi/laravel-whatsapp-api
```

## Step 2: Publish Config

```bash
php artisan vendor:publish --tag=whatsapp-config
```

## Step 3: Configure .env

Ensure these variables are set in `.env`:

```env
WHATSAPP_API_TOKEN=your_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_id
WHATSAPP_WABA_ID=your_waba_id
WHATSAPP_APP_SECRET=your_app_secret
WHATSAPP_VERIFY_TOKEN=your_verify_token
```

## Step 4: Update WhatsAppService

The existing `App\Services\WhatsAppService` can be refactored to use the package internally:

```php
<?php

namespace App\Services;

use Aghfatehi\WhatsApp\Facades\WhatsApp;

class WhatsAppService
{
    public function sendTextMessage(string $to, string $message): bool
    {
        try {
            $result = WhatsApp::messages()->sendText($to, $message);
            return isset($result['messages'][0]['id']);
        } catch (\Throwable $e) {
            \Log::error('WhatsApp send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendMediaMessage(string $to, string $mediaType, string $mediaUrl, ?string $caption = null): bool
    {
        try {
            $result = WhatsApp::messages()->sendMedia($to, $mediaType, $mediaUrl, $caption);
            return isset($result['messages'][0]['id']);
        } catch (\Throwable $e) {
            \Log::error('WhatsApp media send failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function getPhoneNumbers(): array
    {
        return WhatsApp::phoneNumbers()->list();
    }

    public function getWabaId(): ?string
    {
        try {
            // Package auto-configures the client; WABA ID comes from config
            return config('whatsapp.connections.default.waba_id');
        } catch (\Throwable $e) {
            \Log::error('Failed to get WABA ID', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
```

## Step 5: Update WhatsAppController Webhook

The package provides auto-loaded webhook routes at `/api/whatsapp/webhook`.  
Update `routes/api.php` to use the package's `WebhookService` for payload parsing:

```php
use Aghfatehi\WhatsApp\Facades\WhatsApp;

// In your webhook handler:
$parsed = WhatsApp::webhook()->parsePayload($request->all());
// $parsed['messages'] contains structured message data
// $parsed['statuses'] contains delivery status updates
```

## Step 6: Verify Integration

```bash
php artisan whatsapp:setup
php artisan whatsapp:phone-numbers
```

## Database Compatibility

The package is **database-agnostic**. It works with:
- **MySQL** (PrintCommand current) — no changes needed
- **PostgreSQL** — no changes needed
- **MongoDB** — requires `mongodb/laravel-mongodb` package if you store WhatsApp data in MongoDB
- **SQLite** — no changes needed

No migrations are shipped with the package; your existing `conversation_logs`, `orders`, and `customers` tables are used as-is.
