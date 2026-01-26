<?php
/**
 * Připomněnka - Hlavní konfigurace
 *
 * DŮLEŽITÉ: Tento soubor obsahuje citlivé údaje.
 * Na produkci nahraďte hodnoty skutečnými přístupovými údaji.
 */

return [
    // Databázové připojení
    'db' => [
        'host' => 'db.dw169.webglobe.com',
        'name' => 'pripomnenka',
        'user' => 'dizen_cz1',
        'pass' => 'P@rametr44',
        'charset' => 'utf8mb4',
    ],

    // Nastavení aplikace
    'app' => [
        'name' => 'Připomněnka',
        'url' => 'https://pripomnenka.jelenivzeleni.cz',
        'timezone' => 'Europe/Prague',
        'locale' => 'cs_CZ',
        'debug' => false, // Na produkci VŽDY false
    ],

    // Bezpečnostní nastavení
    'security' => [
        'cron_token' => 'VYGENEROVAT_NAHODNY_TOKEN_32_ZNAKU',
        'session_lifetime' => 86400 * 30,  // 30 dní
        'otp_lifetime' => 600,  // 10 minut
        'otp_max_attempts' => 3,
        'max_login_attempts' => 5,
        'lockout_duration' => 900,  // 15 minut
    ],

    // E-mailové nastavení
    'email' => [
        'from_address' => 'pripomnenka@jelenivzeleni.cz',
        'from_name' => 'Jeleni v zeleni',
        // Pro shared hosting - použít mail() nebo SMTP
        'use_smtp' => false,
        'smtp' => [
            'host' => '',
            'port' => 587,
            'user' => '',
            'pass' => '',
            'encryption' => 'tls',
        ],
    ],

    // Cesty v aplikaci
    'paths' => [
        'root' => dirname(__DIR__),
        'public' => dirname(__DIR__) . '/public',
        'src' => dirname(__DIR__) . '/src',
        'views' => dirname(__DIR__) . '/src/Views',
        'storage' => dirname(__DIR__) . '/storage',
        'logs' => dirname(__DIR__) . '/storage/logs',
    ],
];
