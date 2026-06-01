# PROJECT_MAP: aghfatehi/laravel-whatsapp-api

## TECH_STACK

| Layer | Technology | Version |
|---|---|---|
| **PHP** | PHP | ^8.0 |
| **Laravel** | `illuminate/*` | ^9.0 \|\| ^10.0 \|\| ^11.0 \|\| ^12.0 \|\| ^13.0 |
| **HTTP Client** | GuzzleHttp | ^7.0 |
| **Logging** | PSR-3 LoggerInterface | ^1.0 \|\| ^2.0 \|\| ^3.0 |
| **Database** | MySQL / PostgreSQL / SQLite / MongoDB | Any (package is DB-agnostic) |
| **Testing** | PHPUnit + Mockery + Orchestra Testbench | Latest stable |
| **Meta API** | WhatsApp Cloud Graph API | v22.0 |

## ARCHITECTURE

```
┌─────────────────────────────────────────────────────────────────┐
│                    Laravel Application                          │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │              aghfatehi/laravel-whatsapp-api                │  │
│  │                                                           │  │
│  │  WhatsAppManager (Factory)                                │  │
│  │    ├── connection('default') → Client (Guzzle)            │  │
│  │    ├── messages()          → MessageService               │  │
│  │    ├── media()             → MediaService                 │  │
│  │    ├── phoneNumbers()      → PhoneNumberService           │  │
│  │    ├── businessProfile()   → BusinessProfileService       │  │
│  │    ├── templates()         → TemplateService               │  │
│  │    ├── qrCodes()           → QRCodeService                 │  │
│  │    ├── twoStepVerification()→ TwoStepVerificationService   │  │
│  │    ├── conversations()     → ConversationService           │  │
│  │    └── webhook()           → WebhookService                │  │
│  └───────────────────────────────────────────────────────────┘  │
│                              │                                   │
│                              ▼                                   │
│              ┌──────────────────────────────┐                    │
│              │   Meta Graph API v22.0       │                    │
│              │   https://graph.facebook.com  │                    │
│              └──────────────────────────────┘                    │
└─────────────────────────────────────────────────────────────────┘
```

## SYSTEM_FLOW

### Message Flow (Outbound)
```
App → WhatsApp::messages()->sendText('9665...', 'Hello')
   → WhatsAppManager::messages()
   → MessageService::sendText()
   → Client::post('{phone_id}/messages', payload)
   → GuzzleHttp::request('POST', "https://graph.facebook.com/v22.0/{id}/messages")
   → ApiResponse { messages: [{ id: 'wamid.xxx' }] }
```

### Webhook Flow (Inbound)
```
Meta POST /api/whatsapp/webhook { entry: [...] }
   → WebhookService::verifySignature(body, signature)
   → WebhookService::handle(payload)
   → WebhookService::parsePayload(payload)
   → Event::dispatch('whatsapp.message.received', [$message])
```

### Multi-Connection Support
```
WhatsApp::connection('default')->messages()->sendText(...)
WhatsApp::connection('secondary')->messages()->sendText(...)
```

## SERVICES MAP

| Service | Contract | Endpoints Covered |
|---|---|---|
| **MessageService** | `Contracts\MessageService` | `{phone_id}/messages` - text, media, template, interactive, location, contacts, reaction, read |
| **MediaService** | `Contracts\MediaService` | `{phone_id}/media`, `{media_id}` - upload, download, get URL, delete |
| **PhoneNumberService** | `Contracts\PhoneNumberService` | `{waba_id}/phone_numbers`, `{phone_id}/register`, `{phone_id}/deregister`, `{phone_id}/request_pin` |
| **BusinessProfileService** | `Contracts\BusinessProfileService` | `{phone_id}/whatsapp_business_profile` - get, update |
| **TemplateService** | `Contracts\TemplateService` | `{waba_id}/message_templates` - CRUD |
| **QRCodeService** | `Contracts\QRCodeService` | `{phone_id}/whatsapp_qr_codes` - CRUD |
| **TwoStepVerificationService** | `Contracts\TwoStepVerificationService` | `{phone_id}/two_step_verification` - set/delete PIN |
| **ConversationService** | `Contracts\ConversationService` | `{phone_id}/conversations`, `{waba_id}/analytics`, `{waba_id}/pricing` |
| **WebhookService** | `Contracts\WebhookService` | Verification, signature validation, payload parsing, event dispatching |

## ORPHANS & PENDING

| Item | Status | Notes |
|---|---|---|
| ~~Package skeleton~~ | ✅ DONE | composer.json, ServiceProvider, Config, Client, Exceptions |
| ~~Message Service~~ | ✅ DONE | All message types including reactions, read receipts |
| ~~Media Service~~ | ✅ DONE | Upload, download, get URL, delete |
| ~~Phone Number Service~~ | ✅ DONE | List, register, deregister, PIN |
| ~~Business Profile Service~~ | ✅ DONE | Get and update |
| ~~Template Service~~ | ✅ DONE | Full CRUD |
| ~~QR Code Service~~ | ✅ DONE | Full CRUD |
| ~~Two-Step Verification~~ | ✅ DONE | Set PIN, delete PIN |
| ~~Conversation Service~~ | ✅ DONE | List, analytics, pricing |
| ~~Webhook Service~~ | ✅ DONE | Verify, signature, parse, events |
| ~~Artisan Commands~~ | ✅ DONE | setup, phone-numbers, register-webhook |
| ~~Facade~~ | ✅ DONE | WhatsApp:: Facade |
| ~~Routes~~ | ✅ DONE | Auto-loaded webhook routes |
| ~~Tests (Unit)~~ | ✅ DONE | 40+ tests across all services |
| ~~Tests (Feature)~~ | ✅ DONE | 10+ integration tests with Orchestra Testbench |
| Integration with PrintCommand | 🔄 IN PROGRESS | Add repository to composer.json, update WhatsAppService |
| Laravel 9 compat verification | ⏳ PENDING | Test with Laravel 9 environment |
| MongoDB driver support doc | ⏳ PENDING | Document `mongodb/laravel-mongodb` setup |
| Packagist release | ⏳ PENDING | Push to GitHub, tag v1.0.0 |
