<?php

namespace Aghfatehi\WhatsApp\Tests\Unit;

use Aghfatehi\WhatsApp\Data\MessageData;
use PHPUnit\Framework\TestCase;

class DataObjectsTest extends TestCase
{
    public function test_text_message_data(): void
    {
        $data = MessageData::text('966555555555', 'Hello!');
        $array = $data->toArray();

        $this->assertEquals('966555555555', $array['to']);
        $this->assertEquals('text', $array['type']);
        $this->assertEquals('Hello!', $array['text']['body']);
    }

    public function test_media_message_data(): void
    {
        $data = MessageData::media('966555555555', 'image', 'https://example.com/img.jpg', 'Caption');
        $array = $data->toArray();

        $this->assertEquals('image', $array['type']);
        $this->assertEquals('https://example.com/img.jpg', $array['image']['link']);
        $this->assertEquals('Caption', $array['image']['caption']);
    }

    public function test_template_message_data(): void
    {
        $data = MessageData::template('966555555555', 'hello_world', 'en');
        $array = $data->toArray();

        $this->assertEquals('template', $array['type']);
        $this->assertEquals('hello_world', $array['template']['name']);
        $this->assertEquals('en', $array['template']['language']['code']);
    }
}
