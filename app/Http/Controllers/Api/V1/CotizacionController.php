<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cotizacion;
use App\Models\Producto;
use App\Services\NotificacionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controlador API: Cotizaciones
 */
class CotizacionController extends Controller
{
    public function __construct(private readonly NotificacionService $notificacionService)
    {
    }

    public function cancelar($id): JsonResponse
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            return response()->json(['exito' => false, 'mensaje' => 'Cotización no encontrada'], 404);
        }

        if ($cotizacion->estado !== 'Pendiente') {
            return response()->json(['exito' => false, 'mensaje' => 'La cotización ya fue procesada o cancelada anteriormente.'], 400);
        }

        $cotizacion->estado = 'Cancelado';
        $cotizacion->save();

        $this->notificacionService->cotizacionCancelada($cotizacion);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Cotización cancelada permanentemente e ignorada de la agenda.',
        ]);
    }

    public function aprobar($id): JsonResponse
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            return response()->json(['exito' => false, 'mensaje' => 'Cotización no encontrada'], 404);
        }

        if ($cotizacion->estado !== 'Pendiente') {
            return response()->json(['exito' => false, 'mensaje' => 'La cotización ya fue procesada o cancelada anteriormente.'], 400);
        }

        // 1. Encontrar o crear cliente para el CRM (Módulo C)
        $cliente = \App\Models\Cliente::firstOrCreate(
            ['nombre' => $cotizacion->cliente_nombre],
            ['telefono' => 'Por actualizar', 'email' => 'pendiente@actualizar.com']
        );

        // 2. Crear el evento en la Agenda (Módulo B)
        $evento = \App\Models\Evento::create([
            'cliente_id' => $cliente->id,
            'cliente_nombre' => $cliente->nombre,
            'fecha_evento' => $cotizacion->fecha_evento,
            'hora_inicio' => '12:00', // Valor por defecto
            'hora_fin' => '17:00', // Valor por defecto
            'municipio' => $cotizacion->municipio,
            'colonia' => $cotizacion->colonia,
            'calle_numero' => $cotizacion->calle_numero,
            'paquete_contratado' => "Expediente Cotización #" . $cotizacion->id,
            'total_invitados' => $cotizacion->total_invitados,
            'total_precio' => $cotizacion->total_precio,
            'estado' => 'Cotizado',
            'cotizacion_id' => $cotizacion->id,
            'notas' => 'Evento generado automáticamente mediante la aprobación de la Cotización #' . $cotizacion->id
        ]);

        $this->notificacionService->cotizacionAprobada($cotizacion, $evento);

        // 3. Cambiar estado
        $cotizacion->estado = 'Confirmado';
        $cotizacion->save();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Cotización aprobada. El evento ha sido creado en la Agenda y asociado al CRM.',
            'datos' => $evento
        ]);
    }

    public function index(): JsonResponse
    {
        $cotizaciones = Cotizacion::with('productos')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Listado de cotizaciones obtenido correctamente.',
            'datos' => $cotizaciones,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $datos = $request->validate([
                'cliente_nombre'   => 'required|string|max:255',
                'fecha_evento'     => 'required|date',
                'hora_evento'      => 'required|date_format:H:i',
                'municipio'        => 'required|string|max:120',
                'colonia'          => 'required|string|max:120',
                'calle_numero'     => 'required|string|max:160',
                'descripcion_lugar' => 'nullable|string|max:500',
                'total_invitados'  => 'required|integer|min:1|max:10000',
                'distancia_km'     => 'required|numeric|min:0|max:500',
                'numero_empleados' => 'required|integer|min:1|max:50',
                'margen_ganancia_porcentaje' => 'required|numeric|min:0|max:100',
                'tarifa_por_empleado' => 'required|numeric|min:0',
                'tarifa_por_km' => 'required|numeric|min:0',
                'productos'        => 'required|array|min:1',
                'productos.*.producto_id' => 'required|integer|exists:productos,id',
                'productos.*.cantidad'    => 'required|numeric|min:0.01',
                'estado'           => 'nullable|in:Pendiente,Confirmado,Cancelado',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Errores de validacion en la solicitud.',
                'errors' => $e->errors(),
            ], 422);
        }

        $productosIds = collect($datos['productos'])->pluck('producto_id')->unique()->values();
        $productos = Producto::with('insumos')->whereIn('id', $productosIds)->get()->keyBy('id');

        if ($productos->count() !== $productosIds->count()) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Uno o mas productos no existen.',
            ], 422);
        }

        $costoSnacks = 0.00;
        $pivot = [];

        foreach ($datos['productos'] as $item) {
            $producto = $productos[$item['producto_id']];
            $cantidad = (float) $item['cantidad'];
            $costoUnitario = $producto->calcularCostoProduccion();
            $costoSnacks += $costoUnitario * $cantidad;

            $pivot[$producto->id] = [
                'cantidad' => $cantidad,
            ];
        }

        $costoSnacks = round($costoSnacks, 2);
        $costoTransporte = round($datos['tarifa_por_km'] * $datos['distancia_km'], 2);
        $costoNomina = round($datos['tarifa_por_empleado'] * $datos['numero_empleados'], 2);
        $totalOperativo = round($costoSnacks + $costoTransporte + $costoNomina, 2);

        $montoMargen = round($totalOperativo * ($datos['margen_ganancia_porcentaje'] / 100), 2);
        $totalPrecio = round($totalOperativo + $montoMargen, 2);

        $cotizacion = Cotizacion::create([
            'cliente_nombre'  => $datos['cliente_nombre'],
            'fecha_evento'    => $datos['fecha_evento'],
            'hora_evento'     => $datos['hora_evento'],
            'municipio'       => $datos['municipio'],
            'colonia'         => $datos['colonia'],
            'calle_numero'    => $datos['calle_numero'],
            'descripcion_lugar' => $datos['descripcion_lugar'] ?? null,
            'total_invitados' => $datos['total_invitados'],
            'total_precio'    => $totalPrecio,
            'estado'          => $datos['estado'] ?? 'Pendiente',
        ]);

        $cotizacion->productos()->attach($pivot);
        $cotizacion->load('productos');

        return response()->json([
            'exito' => true,
            'mensaje' => 'Cotizacion creada correctamente.',
            'datos' => [
                'cotizacion' => $cotizacion,
                'totales' => [
                    'snacks' => $costoSnacks,
                    'transporte' => $costoTransporte,
                    'nomina' => $costoNomina,
                    'total_operativo' => $totalOperativo,
                    'margen' => $montoMargen,
                    'total_precio' => $totalPrecio,
                ],
            ],
        ], 201);
    }

    public function downloadStoredPdf(int $id)
    {
        $cotizacion = Cotizacion::with('productos.insumos')->find($id);

        if (!$cotizacion) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Cotizacion no encontrada.',
            ], 404);
        }

        $items = [];
        $costoSnacks = 0.00;

        foreach ($cotizacion->productos as $producto) {
            $cantidad = (float) $producto->pivot->cantidad;
            $costoUnitario = $producto->calcularCostoProduccion();
            $subtotal = $costoUnitario * $cantidad;
            $costoSnacks += $subtotal;

            $items[] = [
                'cantidad' => $cantidad,
                'nombre' => $producto->nombre,
                'subtotal' => round($subtotal, 2),
            ];
        }

        $costoSnacks = round($costoSnacks, 2);
        $totalPrecio = (float) $cotizacion->total_precio;
        $logistica = max(round($totalPrecio - $costoSnacks, 2), 0);

        $folio = 'RY-'.str_pad((string) $cotizacion->id, 4, '0', STR_PAD_LEFT);

        $pdf = Pdf::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
        ])->loadView('pdf.cotizacion', [
            'folio' => $folio,
            'fecha_emision' => $cotizacion->created_at?->format('d/m/Y') ?? now()->format('d/m/Y'),
            'cliente' => [
                'nombre' => $cotizacion->cliente_nombre,
            ],
            'evento' => [
                'fecha' => $cotizacion->fecha_evento?->format('Y-m-d') ?? $cotizacion->fecha_evento,
                'hora' => $cotizacion->hora_evento?->format('H:i') ?? $cotizacion->hora_evento,
                'municipio' => $cotizacion->municipio,
                'colonia' => $cotizacion->colonia,
                'calle_numero' => $cotizacion->calle_numero,
                'descripcion_lugar' => $cotizacion->descripcion_lugar,
            ],
            'productos' => $items,
            'totales' => [
                'snacks' => $costoSnacks,
                'logistica' => $logistica,
                'total' => round($totalPrecio, 2),
            ],
        ]);

        return $pdf->download('cotizacion.pdf');
    }
}
