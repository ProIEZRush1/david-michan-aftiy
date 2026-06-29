<?php

namespace App\Http\Controllers;

use App\Services\BotEngine;
use App\Services\GatewayClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class WhatsAppController extends Controller
{
    /** Proxy the gateway's QR/connection state for the /conectar screen. */
    public function qr(GatewayClient $gateway)
    {
        return response()->json($gateway->qr());
    }

    /**
     * Inbound webhook from the local Baileys gateway. Validates the shared secret,
     * ignores groups and empty messages, then drives the sales bot. Returns fast.
     */
    public function inbound(Request $request, BotEngine $engine)
    {
        if ($request->header('x-gateway-token') !== config('bot.gateway_token')) {
            abort(401);
        }

        $from = (string) $request->input('from', '');
        $fromName = $request->input('fromName');
        $text = trim((string) $request->input('text', ''));
        $isGroup = (bool) $request->input('isGroup', false);

        if ($isGroup) {
            return response()->json(['ok' => true]);
        }

        if ($from === '' || $text === '') {
            return response()->json(['ok' => true]);
        }

        $engine->handle($from, $fromName !== null ? (string) $fromName : null, $text);

        return response()->json(['ok' => true]);
    }

    /** The "Conectar WhatsApp" page (scan QR to link the client's number). */
    public function conectar(): InertiaResponse
    {
        return Inertia::render('Conectar');
    }
}
