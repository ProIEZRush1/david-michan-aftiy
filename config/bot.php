<?php

return [
    // Base URL of the local Baileys gateway (same container, localhost only).
    'gateway_url' => env('BOT_GATEWAY_URL', 'http://127.0.0.1:3001'),

    // Shared secret presented in BOTH directions (panel→gateway and gateway→panel)
    // as the `x-gateway-token` header. Must equal the gateway's GATEWAY_TOKEN.
    // Default matches the gateway's fallback so the template works even before the pipeline
    // injects a real per-client token at deploy time.
    'gateway_token' => env('GATEWAY_TOKEN', 'change-me'),
];
