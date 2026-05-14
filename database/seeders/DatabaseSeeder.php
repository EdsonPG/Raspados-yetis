<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Ejecuta todos los seeders en orden:
     *   1. MasterSeeder → Insumos, Productos, Cotizaciones, Eventos
     */
    public function run(): void
    {
        $this->call([
            MasterSeeder::class,
        ]);
    }
}
