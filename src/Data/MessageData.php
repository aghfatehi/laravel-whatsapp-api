<?php

namespace Aghfatehi\WhatsApp\Data;

class MessageData
{
    public function __construct(
        public readonly string $to,
        public readonly string $type,
        public readonly array $payload,
    ) {}

    public static function text(string $to, string $body, bool $previewUrl = false): self
    {
        return new self($to, 'text', [
            'preview_url' => $previewUrl,
            'body' => $body,
        ]);
    }

    public static function media(string $to, string $type, string $link, ?string $caption = null, ?string $filename = null): self
    {
        return new self($to, $type, array_filter([
            'link' => $link,
            'caption' => $caption,
            'filename' => $filename,
        ]));
    }

    public static function template(string $to, string $name, string $languageCode = 'en', array $components = []): self
    {
        return new self($to, 'template', [
            'name' => $name,
            'language' => ['code' => $languageCode],
            'components' => $components,
        ]);
    }

    public function toArray(): array
    {
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $this->to,
            'type' => $this->type,
            $this->type => $this->payload,
        ];

        if ($this->type === 'template') {
            unset($data['template']['components']);
            if (!empty($this->payload['components'])) {
                $data['template']['components'] = $this->payload['components'];
            }
        }

        return $data;
    }
}
