<?php

/*
 * PHPUnit bootstrap — runs BEFORE Laravel boots.
 *
 * The deploy host leaks OS env vars (APP_ENV=production, DB_CONNECTION/DB_DATABASE pointing at
 * the live SQLite file, etc.). Laravel's Dotenv loader is immutable, so it never overrides an
 * env var that already exists in the process — and PHPUnit's <env force="true"> entries are
 * applied too late / via an adapter Laravel doesn't always read. The net effect is the suite
 * running against APP_ENV=production (CSRF active → 419 on every POST) and the real database
 * (duplicate-seed UNIQUE violations). We pin the testing environment here, in every adapter
 * Laravel reads ($_ENV, $_SERVER and getenv), so the suite is hermetic regardless of the host.
 */
$forced = [
    'APP_ENV' => 'testing',
    'APP_DEBUG' => 'true',
    'APP_NAME' => 'David Michan',
    'APP_MAINTENANCE_DRIVER' => 'file',
    'BCRYPT_ROUNDS' => '4',
    'BROADCAST_CONNECTION' => 'null',
    'CACHE_STORE' => 'array',
    'DB_CONNECTION' => 'sqlite',
    'DB_DATABASE' => ':memory:',
    'DB_URL' => '',
    'MAIL_MAILER' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'SESSION_DRIVER' => 'array',
    'PULSE_ENABLED' => 'false',
    'TELESCOPE_ENABLED' => 'false',
    'NIGHTWATCH_ENABLED' => 'false',
];

foreach ($forced as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

require __DIR__.'/../vendor/autoload.php';
