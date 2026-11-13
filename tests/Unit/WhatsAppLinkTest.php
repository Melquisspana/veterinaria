<?php

namespace Tests\Unit;

use App\Support\WhatsApp;
use Tests\TestCase;

class WhatsAppLinkTest extends TestCase
{
    public function test_it_builds_a_wa_me_link_with_the_encoded_message(): void
    {
        $link = WhatsApp::link('+504 9999-8888', 'Hola, ¿cómo está?');

        $this->assertSame('https://wa.me/50499998888?text=Hola%2C%20%C2%BFc%C3%B3mo%20est%C3%A1%3F', $link);
    }

    public function test_it_returns_null_without_a_number(): void
    {
        $this->assertNull(WhatsApp::link(null, 'Hola'));
        $this->assertNull(WhatsApp::link('  ', 'Hola'));
    }
}
