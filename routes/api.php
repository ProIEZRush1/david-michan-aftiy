<?php

use App\Http\Controllers\WhatsAppController;
use Illuminate\Support\Facades\Route;

// API routes carry NO CSRF / web-session middleware — required so the gateway's
// localhost POST to /api/wa/inbound works. JSON error rendering for api/* is
// already configured in bootstrap/app.php.
Route::get('/wa/qr', [WhatsAppController::class, 'qr']);
Route::post('/wa/inbound', [WhatsAppController::class, 'inbound']);
