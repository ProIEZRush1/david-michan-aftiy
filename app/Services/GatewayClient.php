<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin HTTP client for the local Baileys gateway. Both calls present the shared
 * `x-gateway-token` header and fail soft (never throw) so an unreachable gateway
 * can never crash an inbound webhook or a page render.
 */
class GatewayClient
{
    /** Send a plain-text WhatsApp message via the gateway. Returns false on any failure. */
    public function send(string $to, string $text): bool
    {
        try {
            $res = Http::withHeaders(['x-gateway-token' => config('bot.gateway_token')])
                ->timeout(20)
                ->post(rtrim((string) config('bot.gateway_url'), '/').'/send', ['to' => $to, 'text' => $text]);

            return $res->successful();
        } catch (\Throwable $e) {
            Log::warning('gateway send failed', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Proxy the gateway's connection state for the /conectar screen.
     *
     * @return array{status:string,qrDataUrl:?string,me:?string}
     */
    public function qr(): array
    {
        try {
            $res = Http::withHeaders(['x-gateway-token' => config('bot.gateway_token')])
                ->timeout(10)
                ->get(rtrim((string) config('bot.gateway_url'), '/').'/qr');

            return $res->successful()
                ? $res->json()
                : ['status' => 'disconnected', 'qrDataUrl' => null, 'me' => null];
        } catch (\Throwable $e) {
            return ['status' => 'disconnected', 'qrDataUrl' => null, 'me' => null];
        }
    }
}
