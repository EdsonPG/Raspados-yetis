<?php

namespace Database\Seeders;

use App\Models\Evento;
use Illuminate\Database\Seeder;

/**
 * Seeder: 30 eventos aleatorios para el mes actual.
 *
 * Usa EventoFactory para generar datos realistas.
 * Fuerza que ciertos días tengan 2–3 eventos apilados
 * para probar cómo el frontend maneja días ocupados.
 */
class EventoSeeder extends Seeder
{
    public function run(): void
    {
        // ── Limpiar datos existentes ──────────────────
        Evento::query()->delete();

        // ── Generar 60 eventos aleatorios (Abril–Julio 2026) ──
        // Distribuidos en 4 meses: mes actual + próximos 3 meses.
        $anio = (int) date('Y');
        $mes  = (int) date('m');

        // Días con apilamiento forzado: 1 día por cada mes (3 eventos c/u = 12 forzados)
        $diasApiladosPorMes = [
            0 => [8, 15],   // Mes actual: día 8 y 15
            1 => [5, 20],   // Mes +1: día 5 y 20
            2 => [10],      // Mes +2: día 10
            3 => [14],      // Mes +3: día 14
        ];

        // 1) Crear eventos forzados en días apilados (6 días × 3 eventos = 18 forzados)
        foreach ($diasApiladosPorMes as $offset => $dias) {
            $mesObjetivo = $mes + $offset;
            $anioObjetivo = $anio;
            if ($mesObjetivo > 12) {
                $mesObjetivo -= 12;
                $anioObjetivo++;
            }

            foreach ($dias as $dia) {
                $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mesObjetivo, $anioObjetivo);
                $diaReal = min($dia, $diasEnMes);
                $fecha = sprintf('%04d-%02d-%02d', $anioObjetivo, $mesObjetivo, $diaReal);

                Evento::factory()->count(3)->create([
                    'fecha_evento' => $fecha,
                ]);
            }
        }

        // 2) Crear los 42 restantes de forma aleatoria (distribuidos por el Factory)
        Evento::factory()->count(42)->create();

        $total = Evento::count();
        $this->command->info("✅ Datos inyectados: {$total} eventos generados para {$this->rangoTexto($mes)}.");
    }

    /**
     * Genera un texto descriptivo del rango de meses.
     */
    private function rangoTexto(int $mesInicio): string
    {
        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $inicio = $meses[$mesInicio - 1];
        $finIdx = ($mesInicio + 2) % 12; // +3 meses, 0-indexed
        $fin = $meses[$finIdx];
        return "{$inicio} – {$fin} " . date('Y');
    }
}
