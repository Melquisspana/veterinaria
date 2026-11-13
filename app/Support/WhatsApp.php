<?php

namespace App\Support;

class WhatsApp
{
    /**
     * Arma un enlace wa.me con el mensaje prellenado. Devuelve null si no hay número.
     */
    public static function link(?string $number, string $message): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        if ($digits === '') {
            return null;
        }

        $countryCode = preg_replace('/\D+/', '', (string) config('vet.whatsapp_country_code'));

        if ($countryCode !== '' && ! str_starts_with($digits, $countryCode) && ! str_starts_with((string) $number, '+')) {
            $digits = $countryCode.$digits;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
