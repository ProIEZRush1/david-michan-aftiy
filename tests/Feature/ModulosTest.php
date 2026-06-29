<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Faq;
use App\Models\Numero;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'email' => 'david-michan@overcloud.us',
            'name' => 'David Michan',
        ]);
    }

    public function test_dashboard_carga_para_el_admin(): void
    {
        $this->actingAs($this->admin())
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_crear_plan_persiste_en_la_base(): void
    {
        $this->actingAs($this->admin())
            ->post(route('planes.store'), [
                'nombre' => 'Plan Prueba',
                'precio' => 199,
                'datos' => '5 GB',
                'minutos' => 'Ilimitados',
                'sms' => 'Ilimitados',
                'vigencia_dias' => 30,
                'descripcion' => 'Plan de prueba',
                'activo' => true,
            ])
            ->assertRedirect(route('planes.index'));

        // El precio se guarda en centavos.
        $this->assertDatabaseHas('planes', ['nombre' => 'Plan Prueba', 'precio' => 19900]);
    }

    public function test_crear_numero_persiste_en_la_base(): void
    {
        $this->actingAs($this->admin())
            ->post(route('numeros.store'), [
                'numero' => '5599998888',
                'lada' => '55',
                'tipo' => 'nuevo',
                'estado' => 'disponible',
            ])
            ->assertRedirect(route('numeros.index'));

        $this->assertDatabaseHas('numeros', ['numero' => '5599998888', 'estado' => 'disponible']);
    }

    public function test_crear_cliente_persiste_en_la_base(): void
    {
        $this->actingAs($this->admin())
            ->post(route('clientes.store'), [
                'nombre' => 'Cliente Prueba',
                'telefono' => '5215500001111',
                'email' => 'prueba@correo.com',
            ])
            ->assertRedirect(route('clientes.index'));

        $this->assertDatabaseHas('clientes', ['telefono' => '5215500001111', 'nombre' => 'Cliente Prueba']);
    }

    public function test_crear_faq_persiste_en_la_base(): void
    {
        $this->actingAs($this->admin())
            ->post(route('faqs.store'), [
                'pregunta' => '¿Cuánto cuesta?',
                'respuesta' => 'Desde $149 MXN.',
                'palabras_clave' => 'precio, costo',
                'activo' => true,
            ])
            ->assertRedirect(route('faqs.index'));

        $this->assertDatabaseHas('faqs', ['pregunta' => '¿Cuánto cuesta?']);
    }

    public function test_crear_pedido_calcula_total_desde_el_plan(): void
    {
        $plan = Plan::create([
            'nombre' => 'Plan X', 'precio' => 24900, 'vigencia_dias' => 30, 'activo' => true,
        ]);

        $this->actingAs($this->admin())
            ->post(route('pedidos.store'), [
                'cliente' => 'Juan Pedido',
                'telefono' => '5215522223333',
                'plan_id' => $plan->id,
                'estado' => 'iniciado',
            ])
            ->assertRedirect(route('pedidos.index'));

        $this->assertDatabaseHas('pedidos', [
            'cliente' => 'Juan Pedido',
            'plan_id' => $plan->id,
            'total' => 24900,
            'estado' => 'iniciado',
        ]);
    }

    public function test_gestionar_pedido_asigna_numero_y_actualiza_inventario(): void
    {
        $plan = Plan::create(['nombre' => 'Plan Y', 'precio' => 19900, 'vigencia_dias' => 30, 'activo' => true]);
        $numero = Numero::create(['numero' => '5512340000', 'estado' => 'disponible', 'tipo' => 'nuevo']);
        $pedido = Pedido::create([
            'cliente' => 'Ana', 'telefono' => '5215599887766', 'plan_id' => $plan->id, 'estado' => 'pagado', 'total' => 19900,
        ]);

        $this->actingAs($this->admin())
            ->put(route('pedidos.update', $pedido->id), [
                'cliente' => 'Ana', 'telefono' => '5215599887766', 'plan_id' => $plan->id,
                'numero_id' => $numero->id, 'estado' => 'numero_asignado',
            ])
            ->assertRedirect(route('pedidos.index'));

        $this->assertDatabaseHas('pedidos', ['id' => $pedido->id, 'numero_id' => $numero->id, 'estado' => 'numero_asignado']);
        $this->assertDatabaseHas('numeros', ['id' => $numero->id, 'estado' => 'asignado']);
    }

    public function test_conversaciones_se_pueden_escalar_a_humano(): void
    {
        $contacto = \App\Models\BotContact::create(['phone' => '5215512121212', 'step' => 'choosing']);

        $this->actingAs($this->admin())
            ->patch(route('conversaciones.tomar', $contacto->id))
            ->assertRedirect();

        $this->assertDatabaseHas('bot_contacts', ['id' => $contacto->id, 'step' => 'human']);
    }
}
