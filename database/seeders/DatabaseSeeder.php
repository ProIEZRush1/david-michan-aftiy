<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Faq;
use App\Models\Numero;
use App\Models\Pedido;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Siembra idempotente del sistema de venta de líneas telefónicas de David Michan:
     * usuario admin, planes, inventario de números, preguntas frecuentes, clientes y
     * algunos pedidos de ejemplo en distintos estados.
     */
    public function run(): void
    {
        // --- Usuario administrador del panel ---------------------------------
        User::updateOrCreate(
            ['email' => 'david-michan@overcloud.us'],
            [
                'name' => 'David Michan',
                'password' => Hash::make('3WfHBI4dMjKR'),
                'email_verified_at' => now(),
            ],
        );

        // --- Planes de líneas telefónicas (precio en centavos) ---------------
        $planes = [
            [
                'nombre' => 'Línea Esencial',
                'precio' => 14900,
                'datos' => '3 GB',
                'minutos' => 'Minutos ilimitados',
                'sms' => 'SMS ilimitados',
                'vigencia_dias' => 30,
                'descripcion' => 'Ideal para uso diario: redes, mensajes y llamadas sin preocuparte.',
                'orden' => 1,
            ],
            [
                'nombre' => 'Línea Plus',
                'precio' => 24900,
                'datos' => '8 GB',
                'minutos' => 'Minutos ilimitados',
                'sms' => 'SMS ilimitados',
                'vigencia_dias' => 30,
                'descripcion' => 'El más vendido: más datos para navegar, mapas y streaming.',
                'orden' => 2,
            ],
            [
                'nombre' => 'Línea Pro',
                'precio' => 39900,
                'datos' => '20 GB',
                'minutos' => 'Minutos ilimitados',
                'sms' => 'SMS ilimitados',
                'vigencia_dias' => 30,
                'descripcion' => 'Para quienes viven conectados: trabaja y disfruta sin límites.',
                'orden' => 3,
            ],
            [
                'nombre' => 'Línea Ilimitada',
                'precio' => 59900,
                'datos' => 'Datos ilimitados',
                'minutos' => 'Minutos ilimitados',
                'sms' => 'SMS ilimitados',
                'vigencia_dias' => 30,
                'descripcion' => 'Todo ilimitado: la máxima libertad para tu día a día.',
                'orden' => 4,
            ],
        ];

        foreach ($planes as $plan) {
            Plan::updateOrCreate(['nombre' => $plan['nombre']], $plan + ['activo' => true]);
        }

        // --- Inventario de números disponibles -------------------------------
        $numeros = [
            ['numero' => '5512345001', 'lada' => '55', 'tipo' => 'nuevo'],
            ['numero' => '5512345002', 'lada' => '55', 'tipo' => 'nuevo'],
            ['numero' => '5512345003', 'lada' => '55', 'tipo' => 'nuevo'],
            ['numero' => '5512345004', 'lada' => '55', 'tipo' => 'nuevo'],
            ['numero' => '5512345005', 'lada' => '55', 'tipo' => 'nuevo'],
            ['numero' => '8112345010', 'lada' => '81', 'tipo' => 'nuevo'],
            ['numero' => '8112345011', 'lada' => '81', 'tipo' => 'nuevo'],
            ['numero' => '8112345012', 'lada' => '81', 'tipo' => 'nuevo'],
            ['numero' => '3312345020', 'lada' => '33', 'tipo' => 'nuevo'],
            ['numero' => '3312345021', 'lada' => '33', 'tipo' => 'nuevo'],
            ['numero' => '3312345022', 'lada' => '33', 'tipo' => 'nuevo'],
            ['numero' => '5512345030', 'lada' => '55', 'tipo' => 'portabilidad'],
            ['numero' => '5512345031', 'lada' => '55', 'tipo' => 'portabilidad'],
            ['numero' => '8112345032', 'lada' => '81', 'tipo' => 'portabilidad'],
        ];

        foreach ($numeros as $n) {
            Numero::updateOrCreate(['numero' => $n['numero']], $n + ['estado' => 'disponible']);
        }

        // --- Preguntas frecuentes (base de conocimiento del bot) -------------
        $faqs = [
            [
                'pregunta' => '¿Tienen cobertura en todo el país?',
                'respuesta' => "📡 ¡Sí! Operamos sobre la red de mayor cobertura del país, con señal en todas las ciudades principales y la mayoría de las zonas rurales. Si me das tu código postal te confirmo la cobertura en tu zona. 🙂",
                'palabras_clave' => 'cobertura, señal, donde funciona, alcance, red',
                'orden' => 1,
            ],
            [
                'pregunta' => '¿Puedo conservar mi número actual (portabilidad)?',
                'respuesta' => "🔄 ¡Claro! Puedes traer tu número con *portabilidad* sin costo. Solo necesitas tu NIP de portabilidad (lo pides marcando 051 desde tu línea actual) y nosotros hacemos el resto en menos de 24 horas. 📲",
                'palabras_clave' => 'portabilidad, conservar mi numero, portar, traer mi numero, mismo numero, nip',
                'orden' => 2,
            ],
            [
                'pregunta' => '¿Cuánto tardan en activar la línea?',
                'respuesta' => "⚡ La activación es *inmediata*: en cuanto confirmamos tu pago, te asignamos tu número y queda lista para usarse al instante. Si es portabilidad, puede tomar hasta 24 horas. 🙌",
                'palabras_clave' => 'activacion, activar, cuanto tarda, tiempo de activacion, demora',
                'orden' => 3,
            ],
            [
                'pregunta' => '¿Qué formas de pago aceptan?',
                'respuesta' => "💳 Aceptamos tarjeta de crédito/débito, transferencia SPEI y pago en efectivo en tiendas de conveniencia. Te enviamos un *link de pago seguro* directo aquí en el chat. 🔒",
                'palabras_clave' => 'formas de pago, pagar, tarjeta, transferencia, spei, efectivo, como pago',
                'orden' => 4,
            ],
            [
                'pregunta' => '¿Los planes tienen contrato forzoso?',
                'respuesta' => "🆓 ¡Para nada! Todos nuestros planes son de prepago, *sin contratos ni plazos forzosos*. Recargas cuando quieras y cambias de plan cuando lo necesites. 😊",
                'palabras_clave' => 'contrato, plazo forzoso, permanencia, prepago, sin contrato',
                'orden' => 5,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(['pregunta' => $faq['pregunta']], $faq + ['activo' => true]);
        }

        // --- Clientes de ejemplo --------------------------------------------
        $clientes = [
            ['nombre' => 'María González', 'telefono' => '5215559876543', 'email' => 'maria.gonzalez@example.com', 'notas' => 'Cliente recurrente, prefiere portabilidad.'],
            ['nombre' => 'Juan Pérez', 'telefono' => '5215551234567', 'email' => 'juan.perez@example.com', 'notas' => 'Compró línea nueva en CDMX.'],
            ['nombre' => 'Ana Martínez', 'telefono' => '5218112223344', 'email' => 'ana.martinez@example.com', 'notas' => 'Interesada en plan ilimitado.'],
        ];

        foreach ($clientes as $c) {
            Cliente::updateOrCreate(['telefono' => $c['telefono']], $c);
        }

        // --- Pedidos de ejemplo en distintos estados -------------------------
        $this->seedPedidos();
    }

    /** Crea pedidos de muestra y, donde aplica, asigna números del inventario. */
    private function seedPedidos(): void
    {
        if (Pedido::count() > 0) {
            return; // ya sembrados → idempotente
        }

        $esencial = Plan::where('nombre', 'Línea Esencial')->first();
        $plus = Plan::where('nombre', 'Línea Plus')->first();
        $pro = Plan::where('nombre', 'Línea Pro')->first();

        // Pedido entregado con número asignado.
        $num1 = Numero::where('estado', 'disponible')->orderBy('id')->first();
        Pedido::create([
            'plan_id' => $plus?->id,
            'numero_id' => $num1?->id,
            'cliente' => 'María González',
            'telefono' => '5215559876543',
            'email' => 'maria.gonzalez@example.com',
            'estado' => 'entregado',
            'total' => $plus?->precio,
        ]);
        $num1?->update(['estado' => 'asignado']);

        // Pedido con número asignado (recién pagado).
        $num2 = Numero::where('estado', 'disponible')->orderBy('id')->first();
        Pedido::create([
            'plan_id' => $pro?->id,
            'numero_id' => $num2?->id,
            'cliente' => 'Juan Pérez',
            'telefono' => '5215551234567',
            'email' => 'juan.perez@example.com',
            'estado' => 'numero_asignado',
            'total' => $pro?->precio,
        ]);
        $num2?->update(['estado' => 'asignado']);

        // Pedido en pago (link generado, sin número aún).
        Pedido::create([
            'plan_id' => $esencial?->id,
            'cliente' => 'Ana Martínez',
            'telefono' => '5218112223344',
            'email' => 'ana.martinez@example.com',
            'estado' => 'en_pago',
            'total' => $esencial?->precio,
            'link_pago' => rtrim((string) config('app.url'), '/').'/pago/OV00003',
        ]);

        // Pedido recién iniciado.
        Pedido::create([
            'plan_id' => $plus?->id,
            'cliente' => 'Carlos Ramírez',
            'telefono' => '5213334445566',
            'estado' => 'iniciado',
            'total' => $plus?->precio,
        ]);
    }
}
