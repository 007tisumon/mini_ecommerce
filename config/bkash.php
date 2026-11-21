<?php

function bkash_config(): array
{
    $local = [];
    $localConfigPath = __DIR__ . '/bkash.local.php';
    if (is_file($localConfigPath)) {
        $loaded = require $localConfigPath;
        if (is_array($loaded)) {
            $local = $loaded;
        }
    }

    return [
        'username' => (string) ($local['username'] ?? getenv('BKASH_USERNAME') ?: ''),
        'password' => (string) ($local['password'] ?? getenv('BKASH_PASSWORD') ?: ''),
        'app_key' => (string) ($local['app_key'] ?? getenv('BKASH_APP_KEY') ?: ''),
        'app_secret' => (string) ($local['app_secret'] ?? getenv('BKASH_APP_SECRET') ?: ''),
        'base_url' => 'https://tokenized.sandbox.bka.sh/v2/tokenized-checkout',
        'callback_base_url' => (string) ($local['callback_base_url'] ?? getenv('BKASH_CALLBACK_BASE_URL') ?: ''),
    ];
}

function bkash_credentials_missing(): array
{
    $config = bkash_config();
    $missing = [];

    if (trim($config['username']) === '') {
        $missing[] = 'BKASH_USERNAME';
    }
    if (trim($config['password']) === '') {
        $missing[] = 'BKASH_PASSWORD';
    }
    if (trim($config['app_key']) === '') {
        $missing[] = 'BKASH_APP_KEY';
    }
    if (trim($config['app_secret']) === '') {
        $missing[] = 'BKASH_APP_SECRET';
    }

    return $missing;
}
