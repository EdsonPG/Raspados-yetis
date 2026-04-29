<?php

namespace Database\Seeders;

use App\Models\Insumo;
use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * Seeder: Datos de demostración para el Motor de Costeo
 *
 * Crea insumos y productos reales de Raspados Yeti con recetas
 * completas para demostrar el funcionamiento del cotizador.
 */
class CosteoSeeder extends Seeder
{
    /**
     * Ejecutar el seeder.
     */
    public function run(): void
    {
        // ── Limpiar datos existentes ──────────────────
        Producto::query()->delete();
        Insumo::query()->delete();

        // ── Crear Insumos (Materia Prima) ─────────────
        $jarabeFresa = Insumo::create([
            'nombre' => 'Jarabe de Fresa',
            'unidad_medida' => 'litros',
            'costo_adquisicion' => 45.00,
        ]);

        $jarabeMango = Insumo::create([
            'nombre' => 'Jarabe de Mango',
            'unidad_medida' => 'litros',
            'costo_adquisicion' => 48.00,
        ]);

        $jarabeTamarindo = Insumo::create([
            'nombre' => 'Jarabe de Tamarindo',
            'unidad_medida' => 'litros',
            'costo_adquisicion' => 52.00,
        ]);

        $hielo = Insumo::create([
            'nombre' => 'Hielo Triturado',
            'unidad_medida' => 'kg',
            'costo_adquisicion' => 12.00,
        ]);

        $lecheCond = Insumo::create([
            'nombre' => 'Leche Condensada',
            'unidad_medida' => 'litros',
            'costo_adquisicion' => 65.00,
        ]);

        $chamoy = Insumo::create([
            'nombre' => 'Chamoy Premium',
            'unidad_medida' => 'litros',
            'costo_adquisicion' => 55.00,
        ]);

        $vaso8oz = Insumo::create([
            'nombre' => 'Vaso Trole 8oz',
            'unidad_medida' => 'piezas',
            'costo_adquisicion' => 3.50,
        ]);

        $vaso16oz = Insumo::create([
            'nombre' => 'Vaso Trole 16oz',
            'unidad_medida' => 'piezas',
            'costo_adquisicion' => 5.00,
        ]);

        $gomitas = Insumo::create([
            'nombre' => 'Gomitas Surtidas',
            'unidad_medida' => 'kg',
            'costo_adquisicion' => 85.00,
        ]);

        $chilePolvo = Insumo::create([
            'nombre' => 'Chile en Polvo Tajín',
            'unidad_medida' => 'kg',
            'costo_adquisicion' => 95.00,
        ]);

        // ── Crear Productos con Recetas ───────────────

        // Producto 1: Trole de Fresa 8oz
        $trole8 = Producto::create([
            'nombre' => 'Trole de Fresa 8oz',
            'precio_sugerido' => 35.00,
        ]);
        $trole8->insumos()->attach([
            $jarabeFresa->id  => ['cantidad' => 0.2000],
            $hielo->id        => ['cantidad' => 0.1500],
            $lecheCond->id    => ['cantidad' => 0.0300],
            $vaso8oz->id      => ['cantidad' => 1.0000],
            $gomitas->id      => ['cantidad' => 0.0200],
        ]);

        // Producto 2: Trole de Mango 16oz
        $trole16 = Producto::create([
            'nombre' => 'Trole de Mango 16oz',
            'precio_sugerido' => 55.00,
        ]);
        $trole16->insumos()->attach([
            $jarabeMango->id  => ['cantidad' => 0.4000],
            $hielo->id        => ['cantidad' => 0.3000],
            $lecheCond->id    => ['cantidad' => 0.0500],
            $chamoy->id       => ['cantidad' => 0.0200],
            $vaso16oz->id     => ['cantidad' => 1.0000],
            $gomitas->id      => ['cantidad' => 0.0400],
        ]);

        // Producto 3: Raspa de Tamarindo
        $raspa = Producto::create([
            'nombre' => 'Raspa de Tamarindo',
            'precio_sugerido' => 40.00,
        ]);
        $raspa->insumos()->attach([
            $jarabeTamarindo->id => ['cantidad' => 0.2500],
            $hielo->id           => ['cantidad' => 0.2500],
            $chamoy->id          => ['cantidad' => 0.0300],
            $chilePolvo->id      => ['cantidad' => 0.0050],
            $vaso8oz->id         => ['cantidad' => 1.0000],
        ]);

        // Producto 4: Yeti Especial (Premium)
        $yetiEsp = Producto::create([
            'nombre' => 'Yeti Especial Premium',
            'precio_sugerido' => 75.00,
        ]);
        $yetiEsp->insumos()->attach([
            $jarabeFresa->id     => ['cantidad' => 0.1500],
            $jarabeMango->id     => ['cantidad' => 0.1500],
            $jarabeTamarindo->id => ['cantidad' => 0.1000],
            $hielo->id           => ['cantidad' => 0.3500],
            $lecheCond->id       => ['cantidad' => 0.0800],
            $chamoy->id          => ['cantidad' => 0.0400],
            $vaso16oz->id        => ['cantidad' => 1.0000],
            $gomitas->id         => ['cantidad' => 0.0500],
            $chilePolvo->id      => ['cantidad' => 0.0080],
        ]);

        $this->command->info('✅ Datos de demostración creados: 10 insumos y 4 productos con recetas.');
    }
}
