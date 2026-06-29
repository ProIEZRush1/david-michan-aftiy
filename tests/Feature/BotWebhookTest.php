<?php

namespace Tests\Feature;

use App\Models\BotContact;
use App\Models\Numero;
use App\Models\Pedido;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BotWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function token(): string
    {
        return (string) config('bot.gateway_token');
    }

    private function inbound(string $from, string $text, string $name = 'Cliente'): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(['x-gateway-token' => $this->token()])
            ->postJson('/api/wa/inbound', [
                'from' => $from,
                'fromName' => $name,
                'text' => $text,
                'isGroup' => false,
            ]);
    }

    public function test_el_webhook_rechaza_token_invalido(): void
    {
        $this->withHeaders(['x-gateway-token' => 'mal-token'])
            ->postJson('/api/wa/inbound', ['from' => '5215500000000', 'text' => 'hola'])
            ->assertStatus(401);
    }

    public function test_un_mensaje_entrante_genera_respuesta_del_bot(): void
    {
        Http::fake(['*' => Http::response(['ok' => true, 'wa_message_id' => 'X1'], 200)]);

        Plan::create(['nombre' => 'Línea Plus', 'precio' => 24900, 'datos' => '8 GB', 'vigencia_dias' => 30, 'activo' => true]);

        $this->inbound('5215512345678', 'hola')->assertOk();

        // Se registró el contacto y avanzó a "eligiendo".
        $this->assertDatabaseHas('bot_contacts', ['phone' => '5215512345678', 'step' => 'choosing']);

        // El bot respondió por el gateway (POST /send).
        Http::assertSent(fn ($request) => str_contains($request->url(), '/send'));
    }

    public function test_el_bot_responde_preguntas_frecuentes(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Plan::create(['nombre' => 'Línea Plus', 'precio' => 24900, 'vigencia_dias' => 30, 'activo' => true]);
        \App\Models\Faq::create([
            'pregunta' => '¿Tienen cobertura?',
            'respuesta' => 'Sí, cobertura nacional.',
            'palabras_clave' => 'cobertura, señal',
            'activo' => true,
        ]);

        $this->inbound('5215512345000', 'tienen cobertura en mi zona?')->assertOk();

        Http::assertSent(fn ($request) => str_contains((string) ($request->data()['text'] ?? ''), 'cobertura nacional'));
    }

    public function test_flujo_completo_asigna_numero_del_inventario(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        Plan::create(['nombre' => 'Línea Plus', 'precio' => 24900, 'datos' => '8 GB', 'vigencia_dias' => 30, 'activo' => true, 'orden' => 1]);
        Numero::create(['numero' => '5512349999', 'estado' => 'disponible', 'tipo' => 'nuevo']);

        $phone = '5215512347777';
        $this->inbound($phone, 'hola')->assertOk();        // saludo → choosing
        $this->inbound($phone, '1')->assertOk();           // elige plan → confirming (crea pedido)
        $this->inbound($phone, 'sí')->assertOk();          // confirma → en_pago + link
        $this->inbound($phone, 'ya pagué')->assertOk();    // paga → numero_asignado

        $pedido = Pedido::where('telefono', $phone)->latest('id')->first();
        $this->assertNotNull($pedido);
        $this->assertSame('numero_asignado', $pedido->estado);
        $this->assertNotNull($pedido->numero_id);

        // El número quedó marcado como asignado en el inventario.
        $this->assertDatabaseHas('numeros', ['id' => $pedido->numero_id, 'estado' => 'asignado']);

        // El contacto cerró el embudo y se registró como cliente.
        $this->assertDatabaseHas('bot_contacts', ['phone' => $phone, 'step' => 'done']);
        $this->assertDatabaseHas('clientes', ['telefono' => $phone]);
    }
}
