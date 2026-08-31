<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi WhatsApp Gateway — BengkelKu-POS
|--------------------------------------------------------------------------
| Mendukung beberapa provider populer di Indonesia.
| Kalau WA_API_URL / WA_API_KEY kosong, sistem otomatis jalan di MODE SANDBOX
| (pesan hanya ditulis ke storage/logs/laravel.log, tidak terkirim).
*/

return [

    // Provider aktif: 'sandbox' | 'fonnte' | 'wablas' | 'watzap' | 'meta'
    'provider' => env('WA_PROVIDER', 'sandbox'),

    // Kredensial umum
    'url'    => env('WA_API_URL', ''),
    'key'    => env('WA_API_KEY', ''),
    'sender' => env('WA_SENDER', ''),

    // Preset endpoint per provider
    'providers' => [

        'fonnte' => [
            'url'       => 'https://api.fonnte.com/send',
            'auth_type' => 'raw',           // header: Authorization: <token>
            'fields'    => [
                'target'  => 'phone',
                'message' => 'message',
            ],
        ],

        'wablas' => [
            'url'       => 'https://console.wablas.com/api/send-message',
            'auth_type' => 'raw',
            'fields'    => [
                'phone'   => 'phone',
                'message' => 'message',
            ],
        ],

        'watzap' => [
            'url'       => 'https://api.watzap.id/v1/send_message',
            'auth_type' => 'body',          // api_key dikirim di body
            'fields'    => [
                'phone_no' => 'phone',
                'message'  => 'message',
            ],
        ],

        'meta' => [
            'url'       => 'https://graph.facebook.com/v21.0/{PHONE_ID}/messages',
            'auth_type' => 'bearer',        // header: Authorization: Bearer <token>
            'fields'    => [],              // pakai payload JSON khusus Meta
        ],
    ],

    /*
    |----------------------------------------------------------------------
    | Jadwal & Aturan Pengingat
    |----------------------------------------------------------------------
    */
    'reminder' => [
        // Kirim pengingat H- berapa hari sebelum jatuh tempo
        'days_before'   => (int) env('WA_REMINDER_DAYS_BEFORE', 3),

        // Jam pengiriman harian (format 24 jam, WITA/Asia-Makassar)
        'send_at'       => env('WA_REMINDER_SEND_AT', '09:00'),

        // Maksimal pesan per batch supaya tidak kena rate limit provider
        'batch_size'    => (int) env('WA_REMINDER_BATCH', 50),

        // Jeda antar pesan (detik) untuk menghindari blokir
        'delay_seconds' => (int) env('WA_REMINDER_DELAY', 2),

        // Aktif/nonaktifkan pengiriman otomatis
        'enabled'       => env('WA_REMINDER_ENABLED', true),
    ],

    /*
    |----------------------------------------------------------------------
    | Interval Servis Berkala (dipakai saat auto-generate reminder)
    |----------------------------------------------------------------------
    */
    'service_interval' => [
        'motor_km'     => (int) env('SERVICE_INTERVAL_MOTOR_KM', 2000),
        'motor_days'   => (int) env('SERVICE_INTERVAL_MOTOR_DAYS', 60),
        'mobil_km'     => (int) env('SERVICE_INTERVAL_MOBIL_KM', 5000),
        'mobil_days'   => (int) env('SERVICE_INTERVAL_MOBIL_DAYS', 120),
    ],
];
