<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Insumo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador API: Inventario y Logística — Módulo D
 *
 * Provee proyecciones inteligentes de consumo de insumos basadas en
 * eventos futuros programados y permite registrar reabastecimientos ágiles.
 */
class InventarioController extends Controller
{
    /**
     * Obtener el estado global del inventario y las proyecciones de stock.
     *
     * GET /api/v1/inventario/proyeccion
     */
    public function proyeccion(): JsonResponse
    {
        $insumos = Insumo::orderBy('nombre')->get();

        // Obtener eventos futuros programados que aún no descuentan inventario
        $eventosFuturos = Evento::with('cotizacion.productos.insumos')
            ->where('inventario_descontado', false)
            ->whereIn('estado', [Evento::ESTADO_COTIZADO, Evento::ESTADO_ANTICIPO_PAGADO, Evento::ESTADO_CONFIRMADO])
            ->get();

        // Mapear consumo proyectado por insumo_id
        $consumoPorInsumo = [];
        $eventosImpactando = 0;

        foreach ($eventosFuturos as $evento) {
            if ($evento->cotizacion && $evento->cotizacion->productos && $evento->cotizacion->productos->isNotEmpty()) {
                $eventosImpactando++;
                foreach ($evento->cotizacion->productos as $producto) {
                    $cantidadProducto = (float) $producto->pivot->cantidad;
                    foreach ($producto->insumos as $insumo) {
                        $cantidadInsumoReceta = (float) $insumo->pivot->cantidad;
                        $consumoUnidad = $cantidadProducto * $cantidadInsumoReceta;

                        if (!isset($consumoPorInsumo[$insumo->id])) {
                            $consumoPorInsumo[$insumo->id] = 0.0;
                        }
                        $consumoPorInsumo[$insumo->id] += $consumoUnidad;
                    }
                }
            }
        }

        $insumosProyectados = $insumos->map(function ($insumo) use ($consumoPorInsumo) {
            $stockActual = (float) $insumo->stock_actual;
            $stockMinimo = (float) $insumo->stock_minimo;
            $consumoProyectado = $consumoPorInsumo[$insumo->id] ?? 0.0;
            $stockProyectado = $stockActual - $consumoProyectado;

            $estado = 'optimo';
            if ($stockProyectado <= 0) {
                $estado = 'agotado';
            } elseif ($stockProyectado <= $stockMinimo) {
                $estado = 'critico';
            } elseif ($stockActual <= $stockMinimo) {
                $estado = 'alerta_actual';
            }

            return [
                'id' => $insumo->id,
                'nombre' => $insumo->nombre,
                'categoria' => $insumo->categoria ?: 'General',
                'unidad_medida' => $insumo->unidad_medida,
                'stock_actual' => round($stockActual, 2),
                'stock_minimo' => round($stockMinimo, 2),
                'consumo_proyectado' => round($consumoProyectado, 2),
                'stock_proyectado' => round($stockProyectado, 2),
                'estado' => $estado,
            ];
        });

        // Calcular métricas/KPIs globales
        $totalInsumos = $insumos->count();
        $criticos = $insumosProyectados->filter(fn($i) => in_array($i['estado'], ['agotado', 'critico', 'alerta_actual']))->count();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Proyección de inventario calculada exitosamente.',
            'datos' => [
                'kpis' => [
                    'total_insumos' => $totalInsumos,
                    'insumos_criticos' => $criticos,
                    'eventos_impactando' => $eventosImpactando,
                ],
                'proyecciones' => $insumosProyectados->values(),
            ],
        ]);
    }

    /**
     * Registrar reabastecimiento rápido de un insumo.
     *
     * POST /api/v1/inventario/reabastecer
     */
    public function reabastecer(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'insumo_id' => 'required|integer|exists:insumos,id',
            'cantidad' => 'required|numeric|min:0.01',
        ]);

        $insumo = Insumo::find($datos['insumo_id']);
        $insumo->stock_actual = (float) $insumo->stock_actual + (float) $datos['cantidad'];
        $insumo->save();

        return response()->json([
            'exito' => true,
            'mensaje' => "Stock de '{$insumo->nombre}' reabastecido correctamente. Nuevo stock: " . round($insumo->stock_actual, 2) . " {$insumo->unidad_medida}.",
            'datos' => $insumo,
        ]);
    }
}
