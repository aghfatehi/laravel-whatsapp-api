# aghfatehi/laravel-whatsapp-api

<p align="center">
    <a href="https://packagist.org/packages/aghfatehi/laravel-whatsapp-api"><img src="https://img.shields.io/packagist/v/aghfatehi/laravel-whatsapp-api.svg?style=flat-square" alt="Packagist Version"></a>
    <a href="https://packagist.org/packages/aghfatehi/laravel-whatsapp-api"><img src="https://img.shields.io/packagist/dt/aghfatehi/laravel-whatsapp-api.svg?style=flat-square" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/aghfatehi/laravel-whatsapp-api"><img src="https://img.shields.io/packagist/php-v/aghfatehi/laravel-whatsapp-api?style=flat-square" alt="PHP Version"></a>
    <a href="https://packagist.org/packages/aghfatehi/laravel-whatsapp-api"><img src="https://img.shields.io/badge/Laravel-9~13-red?style=flat-square&logo=laravel" alt="Laravel"></a>
    <a href="https://github.com/aghfatehi/laravel-whatsapp-api/blob/main/LICENSE"><img src="https://img.shields.io/github/license/aghfatehi/laravel-whatsapp-api?style=flat-square" alt="License"></a>
    <a href="https://github.com/aghfatehi/laravel-whatsapp-api/actions"><img src="https://img.shields.io/github/actions/workflow/status/aghfatehi/laravel-whatsapp-api/tests.yml?style=flat-square&label=tests" alt="Tests"></a>
</p>

Meta WhatsApp Cloud API integration for Laravel 9–13.  
Supports all API services: messaging, media, templates, phone numbers, business profile, webhooks, QR codes, two-step verification, and conversations.

## Installation

```bash
composer require aghfatehi/laravel-whatsapp-api
php artisan vendor:publish --tag=whatsapp-config
```

## Configuration

Add to your `.env`:

```env
WHATSAPP_API_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_WABA_ID=
WHATSAPP_APP_SECRET=
WHATSAPP_VERIFY_TOKEN=
```

## Quick Start

```php
use Aghfatehi\WhatsApp\Facades\WhatsApp;

// Send a text message
WhatsApp::messages()->sendText('966555555555', 'Hello!');

// Send a media message (image, document, audio, video)
WhatsApp::messages()->sendMedia('966555555555', 'image', 'https://example.com/photo.jpg', 'Caption');

// Send a template message
WhatsApp::messages()->sendTemplate('966555555555', 'hello_world', 'en');

// Send a location
WhatsApp::messages()->sendLocation('966555555555', 24.7136, 46.6753, 'Riyadh', 'Saudi Arabia');

// Send an interactive (button/list)
WhatsApp::messages()->sendInteractive('966555555555', [
    'type' => 'button',
    'body' => ['text' => 'Choose:'],
    'action' => [
        'buttons' => [
            ['type' => 'reply', 'reply' => ['id' => '1', 'title' => 'Yes']],
            ['type' => 'reply', 'reply' => ['id' => '2', 'title' => 'No']],
        ],
    ],
]);

// Send reaction
WhatsApp::messages()->sendReaction('966555555555', 'wamid.xxx', '👍');

// Mark message as read
WhatsApp::messages()->markAsRead('wamid.xxx');
```

## All Services

### Messages
```php
WhatsApp::messages()->sendText($to, $body, $previewUrl);
WhatsApp::messages()->sendMedia($to, $type, $url, $caption, $filename);
WhatsApp::messages()->sendMediaById($to, $type, $mediaId, $caption, $filename);
WhatsApp::messages()->sendTemplate($to, $name, $lang, $components);
WhatsApp::messages()->sendInteractive($to, $data);
WhatsApp::messages()->sendLocation($to, $lat, $lng, $name, $address);
WhatsApp::messages()->sendContacts($to, $contacts);
WhatsApp::messages()->sendReaction($to, $messageId, $emoji);
WhatsApp::messages()->markAsRead($messageId);
```

### Media
```php
WhatsApp::media()->upload($filePath, $type);     // Upload to WhatsApp servers
WhatsApp::media()->getUrl($mediaId);               // Get download URL
WhatsApp::media()->download($mediaId);             // Download raw content
WhatsApp::media()->delete($mediaId);               // Delete from WhatsApp
```

### Phone Numbers
```php
WhatsApp::phoneNumbers()->list();                  // All phone numbers
WhatsApp::phoneNumbers()->get($phoneNumberId);      // Single number details
WhatsApp::phoneNumbers()->requestPin($phoneNumberId);
WhatsApp::phoneNumbers()->verifyPin($phoneNumberId, $pin);
WhatsApp::phoneNumbers()->deregister($phoneNumberId);
```

### Business Profile
```php
WhatsApp::businessProfile()->get($phoneNumberId);
WhatsApp::businessProfile()->update($phoneNumberId, $data);
```

### Templates
```php
WhatsApp::templates()->list($status, $limit);
WhatsApp::templates()->get($templateId);
WhatsApp::templates()->create($data);
WhatsApp::templates()->update($templateId, $data);
WhatsApp::templates()->delete($templateId);
```

### QR Codes
```php
WhatsApp::qrCodes()->create($prefilledMessage);
WhatsApp::qrCodes()->list();
WhatsApp::qrCodes()->get($qrCodeId);
WhatsApp::qrCodes()->update($qrCodeId, $data);
WhatsApp::qrCodes()->delete($qrCodeId);
```

### Two-Step Verification
```php
WhatsApp::twoStepVerification()->setPin($phoneNumberId, $pin);
WhatsApp::twoStepVerification()->deletePin($phoneNumberId);
```

### Conversations
```php
WhatsApp::conversations()->list($phoneNumberId, $filters);
WhatsApp::conversations()->getAnalytics($phoneNumberId, $dimensions);
WhatsApp::conversations()->getPricing($phoneNumberId);
```

### Webhook
```php
WhatsApp::webhook()->verifyToken($mode, $token, $challenge);
WhatsApp::webhook()->verifySignature($body, $signature);
WhatsApp::webhook()->parsePayload($payload);
WhatsApp::webhook()->handle($payload);
```

The package auto-registers these routes:
- `GET  /api/whatsapp/webhook` — webhook verification
- `POST /api/whatsapp/webhook` — incoming messages + status updates

## Multi-Connection

```php
WhatsApp::connection('default')->messages()->sendText(...);
WhatsApp::connection('secondary')->messages()->sendText(...);
```

Define connections in `config/whatsapp.php`:

```php
'connections' => [
    'default' => [
        'api_token' => env('WHATSAPP_API_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'waba_id' => env('WHATSAPP_WABA_ID'),
    ],
    'secondary' => [
        'api_token' => env('WHATSAPP_API_TOKEN_2'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID_2'),
        'waba_id' => env('WHATSAPP_WABA_ID_2'),
    ],
],
```

## Artisan Commands

```bash
php artisan whatsapp:setup              # Validate configuration
php artisan whatsapp:phone-numbers      # List connected phone numbers
php artisan whatsapp:register-webhook   # Register webhook URL
```

## Database Support

The package is **database-agnostic** — it does not ship migrations.  
You can store WhatsApp data in any database:
- **MySQL** / **MariaDB** — default, no changes needed
- **PostgreSQL** — no changes needed
- **SQLite** — no changes needed
- **MongoDB** — add `mongodb/laravel-mongodb` package

## Testing

```bash
composer test
# or
vendor/bin/phpunit
```

## License

MIT
