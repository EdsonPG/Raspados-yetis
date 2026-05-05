<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Controlador API: Eventos — Módulo B
 *
 * CRUD completo para la gestión de eventos programados.
 * Soporta filtrado por rango de fechas (para vistas de calendario)
 * y transiciones de estado con validación de flujo de trabajo.
 */
class EventoController extends Controller
{
    /**
     * Listar eventos con filtros opcionales.
     *
     * Query params:
     *   - desde (date): Fecha inicio del rango (inclusive)
     *   - hasta (date): Fecha fin del rango (inclusive)
     *   - estado (string): Filtrar por estado específico
     *   - mes (int): Filtrar por mes (1-12)
     *   - anio (int): Filtrar por año
     *
     * GET /api/v1/eventos
     */
    public function index(Request $request): JsonResponse
    {
        $query = Evento::query()->with('cotizacion');

        // ── Filtro por rango de fechas ───────────────────────────────
        if ($request->filled('desde') && $request->filled('hasta')) {
            $query->whereBetween('fecha_evento', [
                $request->input('desde'),
                $request->input('hasta'),
            ]);
        }

        // ── Filtro por mes y año (para vista de calendario mensual) ──
        if ($request->filled('mes') && $request->filled('anio')) {
            $query->whereMonth('fecha_evento', $request->input('mes'))
                  ->whereYear('fecha_evento', $request->input('anio'));
        }

        // ── Filtro por estado ────────────────────────────────────────
        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        $eventos = $query->orderBy('fecha_evento')
                         ->orderBy('hora_inicio')
                         ->get();

        return response()->json([
            'exito'   => true,
            'mensaje' => 'Listado de eventos obtenido correctamente.',
            'datos'   => $eventos,
        ]);
    }

    /**
     * Crear un nuevo evento.
     *
     * POST /api/v1/eventos
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $datos = $request->validate([
                'cliente_nombre'     => 'required|string|max:255',
                'cliente_telefono'   => 'nullable|string|max:20',
                'fecha_evento'       => 'required|date',
                'hora_inicio'        => 'required|date_format:H:i',
                'hora_fin'           => 'nullable|date_format:H:i|after:hora_inicio',
                'municipio'          => 'required|string|max:120',
                'colonia'            => 'required|string|max:120',
                'calle_numero'       => 'required|string|max:160',
                'descripcion_lugar'  => 'nullable|string|max:500',
                'paquete_contratado' => 'required|string|max:255',
                'total_invitados'    => 'required|integer|min:1|max:10000',
                'total_precio'       => 'required|numeric|min:0',
                'estado'             => ['nullable', Rule::in(Evento::ESTADOS)],
                'notas'              => 'nullable|string|max:2000',
                'cotizacion_id'      => 'nullable|integer|exists:cotizaciones,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Errores de validacion en la solicitud.',
                'errors'  => $e->errors(),
            ], 422);
        }

        $evento = Evento::create($datos);
        $evento->load('cotizacion');

        return response()->json([
            'exito'   => true,
            'mensaje' => "Evento para '{$evento->cliente_nombre}' creado correctamente.",
            'datos'   => $evento,
        ], 201);
    }

    /**
     * Ver detalle de un evento.
     *
     * GET /api/v1/eventos/{id}
     */
    public function show(int $id): JsonResponse
    {
        $evento = Evento::with('cotizacion')->find($id);

        if (!$evento) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Evento no encontrado.',
            ], 404);
        }

        return response()->json([
            'exito'   => true,
            'mensaje' => 'Detalle del evento obtenido correctamente.',
            'datos'   => $evento,
        ]);
    }

    /**
     * Actualizar un evento existente.
     *
     * PUT /api/v1/eventos/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Evento no encontrado.',
            ], 404);
        }

        try {
            $datos = $request->validate([
                'cliente_nombre'     => 'sometimes|required|string|max:255',
                'cliente_telefono'   => 'nullable|string|max:20',
                'fecha_evento'       => 'sometimes|required|date',
                'hora_inicio'        => 'sometimes|required|date_format:H:i',
                'hora_fin'           => 'nullable|date_format:H:i',
                'municipio'          => 'sometimes|required|string|max:120',
                'colonia'            => 'sometimes|required|string|max:120',
                'calle_numero'       => 'sometimes|required|string|max:160',
                'descripcion_lugar'  => 'nullable|string|max:500',
                'paquete_contratado' => 'sometimes|required|string|max:255',
                'total_invitados'    => 'sometimes|required|integer|min:1|max:10000',
                'total_precio'       => 'sometimes|required|numeric|min:0',
                'notas'              => 'nullable|string|max:2000',
                'cotizacion_id'      => 'nullable|integer|exists:cotizaciones,id',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Errores de validacion en la solicitud.',
                'errors'  => $e->errors(),
            ], 422);
        }

        $evento->update($datos);
        $evento->load('cotizacion');

        return response()->json([
            'exito'   => true,
            'mensaje' => "Evento #{$evento->id} actualizado correctamente.",
            'datos'   => $evento,
        ]);
    }

    /**
     * Eliminar un evento.
     *
     * DELETE /api/v1/eventos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Evento no encontrado.',
            ], 404);
        }

        $nombre = $evento->cliente_nombre;
        $evento->delete();

        return response()->json([
            'exito'   => true,
            'mensaje' => "Evento de '{$nombre}' eliminado correctamente.",
        ]);
    }

    /**
     * Cambiar el estado de un evento (flujo de trabajo).
     *
     * Valida que la transición sea permitida según el flujo:
     *   Cotizado → Anticipo Pagado → Confirmado → Completado
     *
     * PATCH /api/v1/eventos/{id}/estado
     */
    public function cambiarEstado(Request $request, int $id): JsonResponse
    {
        $evento = Evento::find($id);

        if (!$evento) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Evento no encontrado.',
            ], 404);
        }

        try {
            $datos = $request->validate([
                'estado' => ['required', Rule::in(Evento::ESTADOS)],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Estado no valido.',
                'errors'  => $e->errors(),
            ], 422);
        }

        $nuevoEstado = $datos['estado'];
        $estadoAnterior = $evento->estado;

        if (!$evento->cambiarEstado($nuevoEstado)) {
            return response()->json([
                'exito'   => false,
                'mensaje' => "No se puede cambiar de '{$estadoAnterior}' a '{$nuevoEstado}'. "
                           . "Transiciones permitidas desde '{$estadoAnterior}': "
                           . implode(', ', Evento::TRANSICIONES[$estadoAnterior] ?? [])
                           . '.',
            ], 422);
        }

        return response()->json([
            'exito'   => true,
            'mensaje' => "Estado del evento cambiado de '{$estadoAnterior}' a '{$nuevoEstado}'.",
            'datos'   => $evento->fresh('cotizacion'),
        ]);
    }
}
