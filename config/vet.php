<?php

return [
    // Código ISO de la moneda usada para mostrar los cobros (USD, MXN, HNL, COP, ...).
    'currency' => env('APP_CURRENCY', 'USD'),

    // Días de anticipación para avisar de vacunas por vencer.
    'vaccine_reminder_days' => (int) env('VET_VACCINE_REMINDER_DAYS', 7),

    // Código de país por defecto para armar enlaces de WhatsApp si el número no lo trae.
    'whatsapp_country_code' => env('VET_WHATSAPP_COUNTRY_CODE', ''),

    // Disco donde se guardan fotos y archivos clínicos: "public" en local, "s3" (bucket) en Laravel Cloud.
    // En cualquier disco distinto de "public" los archivos son privados y se ven con enlaces temporales.
    'uploads_disk' => env('VET_UPLOADS_DISK', 'public'),
];
