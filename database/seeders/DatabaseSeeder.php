<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Example plans (cents) so the sales bot always has something to show in a demo.
        $plans = [
            ['nombre' => 'Básico', 'precio' => 99900, 'descripcion' => 'Ideal para empezar: lo esencial para tu negocio.', 'orden' => 1],
            ['nombre' => 'Profesional', 'precio' => 199900, 'descripcion' => 'El más popular: más funciones y soporte prioritario.', 'orden' => 2],
            ['nombre' => 'Premium', 'precio' => 349900, 'descripcion' => 'Todo incluido: máximo alcance y atención dedicada.', 'orden' => 3],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['nombre' => $plan['nombre']],
                [
                    'precio' => $plan['precio'],
                    'descripcion' => $plan['descripcion'],
                    'activo' => true,
                    'orden' => $plan['orden'],
                ],
            );
        }
    }
}
