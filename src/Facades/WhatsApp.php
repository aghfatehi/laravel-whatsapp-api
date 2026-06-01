<?php

namespace Aghfatehi\WhatsApp\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Aghfatehi\WhatsApp\WhatsAppManager connection(?string $name = null)
 * @method static \Aghfatehi\WhatsApp\Contracts\MessageService messages()
 * @method static \Aghfatehi\WhatsApp\Contracts\MediaService media()
 * @method static \Aghfatehi\WhatsApp\Contracts\PhoneNumberService phoneNumbers()
 * @method static \Aghfatehi\WhatsApp\Contracts\BusinessProfileService businessProfile()
 * @method static \Aghfatehi\WhatsApp\Contracts\TemplateService templates()
 * @method static \Aghfatehi\WhatsApp\Contracts\QRCodeService qrCodes()
 * @method static \Aghfatehi\WhatsApp\Contracts\TwoStepVerificationService twoStepVerification()
 * @method static \Aghfatehi\WhatsApp\Contracts\ConversationService conversations()
 * @method static \Aghfatehi\WhatsApp\Contracts\WebhookService webhook()
 * @method static \Aghfatehi\WhatsApp\Http\Client getClient()
 *
 * @see \Aghfatehi\WhatsApp\WhatsAppManager
 */
class WhatsApp extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'whatsapp';
    }
}
