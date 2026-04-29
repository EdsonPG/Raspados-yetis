<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador API: Productos (Snacks Finales)
 *
 * Gestiona las operaciones CRUD para los productos terminados
 * y expone el motor de costeo para calcular precios de producción.
 *
 * Prefijo de rutas: /api/v1/productos
 */
class ProductoController extends Controller
{
    /**
     * Listar todos los productos con paginación.
     *
     * Devuelve una lista paginada de todos los productos registrados.
     * Incluye la cuenta de insumos en la receta de cada producto.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * GET /api/v1/productos
     * GET /api/v1/productos?page=2
     * GET /api/v1/productos?per_page=25
     */
    public function index(Request $request): JsonResponse
    {
        $porPagina = $request->input('per_page', 15);

        $productos = Producto::withCount('insumos')
            ->orderBy('nombre')
            ->paginate($porPagina);

        return response()->json([
            'exito'   => true,
            'mensaje' => 'Lista de productos obtenida correctamente.',
            'datos'   => $productos,
        ]);
    }

    /**
     * Registrar un nuevo producto.
     *
     * Crea un nuevo producto y opcionalmente asigna su receta (insumos).
     * La receta se envía como un array de objetos con insumo_id y cantidad.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * POST /api/v1/productos
     * Body: {
     *   "nombre": "Trole de 8oz",
     *   "precio_sugerido": 35.00,
     *   "receta": [
     *     { "insumo_id": 1, "cantidad": 0.25 },
     *     { "insumo_id": 2, "cantidad": 0.15 }
     *   ]
     * }
     */
    public function store(Request $request): JsonResponse
    {
        // Validación de datos de entrada
        $datosValidados = $request->validate([
            'nombre'             => 'required|string|max:255',
            'precio_sugerido'    => 'nullable|numeric|min:0',
            // Validación de la receta (opcional al crear)
            'receta'             => 'nullable|array',
            'receta.*.insumo_id' => 'required_with:receta|integer|exists:insumos,id',
            'receta.*.cantidad'  => 'required_with:receta|numeric|min:0.0001',
        ]);

        // Crear el producto
        $producto = Producto::create([
            'nombre'          => $datosValidados['nombre'],
            'precio_sugerido' => $datosValidados['precio_sugerido'] ?? 0.00,
        ]);

        // Si se envió receta, sincronizar los insumos en la tabla pivote
        if (!empty($datosValidados['receta'])) {
            $recetaParaPivote = [];
            foreach ($datosValidados['receta'] as $ingrediente) {
                $recetaParaPivote[$ingrediente['insumo_id']] = [
                    'cantidad' => $ingrediente['cantidad'],
                ];
            }
            $producto->insumos()->attach($recetaParaPivote);
        }

        // Cargar relaciones para la respuesta
        $producto->load('insumos');

        return response()->json([
            'exito'   => true,
            'mensaje' => "Producto '{$producto->nombre}' creado correctamente.",
            'datos'   => $producto,
        ], 201);
    }

    /**
     * Mostrar un producto específico con su receta completa.
     *
     * Busca un producto por su ID e incluye todos los insumos
     * de su receta con las cantidades de la tabla pivote.
     *
     * @param int $id
     * @return JsonResponse
     *
     * GET /api/v1/productos/{id}
     */
    public function show(int $id): JsonResponse
    {
        $producto = Producto::with('insumos')->find($id);

        if (!$producto) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Producto no encontrado.',
            ], 404);
        }

        return response()->json([
            'exito'   => true,
            'mensaje' => 'Producto encontrado.',
            'datos'   => $producto,
        ]);
    }

    /**
     * Actualizar un producto existente y/o su receta.
     *
     * Permite actualizar los datos del producto y reemplazar
     * completamente su receta si se envía el campo 'receta'.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     *
     * PUT /api/v1/productos/{id}
     * Body: {
     *   "nombre": "Trole de 8oz Premium",
     *   "receta": [
     *     { "insumo_id": 1, "cantidad": 0.30 },
     *     { "insumo_id": 3, "cantidad": 1 }
     *   ]
     * }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Producto no encontrado.',
            ], 404);
        }

        // Validación parcial: solo se validan los campos enviados
        $datosValidados = $request->validate([
            'nombre'             => 'sometimes|required|string|max:255',
            'precio_sugerido'    => 'sometimes|nullable|numeric|min:0',
            'receta'             => 'sometimes|array',
            'receta.*.insumo_id' => 'required_with:receta|integer|exists:insumos,id',
            'receta.*.cantidad'  => 'required_with:receta|numeric|min:0.0001',
        ]);

        // Actualizar campos del producto (sin la receta)
        $producto->update(collect($datosValidados)->except('receta')->toArray());

        // Si se envió receta, reemplazar completamente con sync
        if (isset($datosValidados['receta'])) {
            $recetaParaPivote = [];
            foreach ($datosValidados['receta'] as $ingrediente) {
                $recetaParaPivote[$ingrediente['insumo_id']] = [
                    'cantidad' => $ingrediente['cantidad'],
                ];
            }
            // sync() reemplaza todas las relaciones existentes
            $producto->insumos()->sync($recetaParaPivote);
        }

        // Recargar relaciones para la respuesta
        $producto->load('insumos');

        return response()->json([
            'exito'   => true,
            'mensaje' => "Producto '{$producto->nombre}' actualizado correctamente.",
            'datos'   => $producto,
        ]);
    }

    /**
     * Eliminar un producto.
     *
     * Elimina el registro del producto. Gracias al onDelete('cascade')
     * en la tabla pivote, la receta asociada se elimina automáticamente.
     *
     * @param int $id
     * @return JsonResponse
     *
     * DELETE /api/v1/productos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $producto = Producto::find($id);

        if (!$producto) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Producto no encontrado.',
            ], 404);
        }

        $nombreProducto = $producto->nombre;
        $producto->delete();

        return response()->json([
            'exito'   => true,
            'mensaje' => "Producto '{$nombreProducto}' eliminado correctamente.",
        ]);
    }

    /**
     * Motor de Costeo: Calcular el costo base de producción de un producto.
     *
     * Este es el endpoint central del Motor de Costeo. Realiza lo siguiente:
     * 1. Busca el producto por su ID.
     * 2. Carga todos los insumos asociados mediante la relación belongsToMany (receta).
     * 3. Para cada insumo, multiplica su costo_adquisicion × cantidad (pivot).
     * 4. Suma todos los costos parciales para obtener el costo real de producción.
     * 5. Devuelve el desglose completo y el total en formato JSON.
     *
     * @param int $id - ID del producto a costear
     * @return JsonResponse
     *
     * GET /api/v1/productos/{id}/costo-base
     *
     * Respuesta exitosa:
     * {
     *   "exito": true,
     *   "datos": {
     *     "producto": { "id": 1, "nombre": "Trole de 8oz" },
     *     "desglose": [
     *       { "insumo": "Jarabe de Fresa", "cantidad": 0.25, "unidad": "litros",
     *         "costo_unitario": 50.00, "subtotal": 12.50 }
     *     ],
     *     "costo_produccion_base": 17.75,
     *     "precio_sugerido": 35.00,
     *     "margen_ganancia": 17.25
     *   }
     * }
     */
    public function calcularCostoBase(int $id): JsonResponse
    {
        // Buscar el producto con sus insumos (receta)
        $producto = Producto::with('insumos')->find($id);

        if (!$producto) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Producto no encontrado.',
            ], 404);
        }

        // Verificar que el producto tiene receta
        if ($producto->insumos->isEmpty()) {
            return response()->json([
                'exito'   => false,
                'mensaje' => "El producto '{$producto->nombre}' no tiene insumos asignados en su receta. "
                           . 'No es posible calcular el costo de producción.',
                'datos'   => [
                    'producto'              => [
                        'id'     => $producto->id,
                        'nombre' => $producto->nombre,
                    ],
                    'desglose'              => [],
                    'costo_produccion_base' => 0.00,
                    'precio_sugerido'       => (float) $producto->precio_sugerido,
                    'margen_ganancia'       => (float) $producto->precio_sugerido,
                ],
            ], 422);
        }

        // Construir el desglose del costeo línea por línea
        $desglose = [];
        $costoTotal = 0.00;

        foreach ($producto->insumos as $insumo) {
            // Multiplicar costo del insumo × cantidad fraccionada de la receta
            $subtotal = $insumo->costo_adquisicion * $insumo->pivot->cantidad;
            $costoTotal += $subtotal;

            $desglose[] = [
                'insumo_id'      => $insumo->id,
                'insumo'         => $insumo->nombre,
                'cantidad'       => (float) $insumo->pivot->cantidad,
                'unidad_medida'  => $insumo->unidad_medida,
                'costo_unitario' => (float) $insumo->costo_adquisicion,
                'subtotal'       => round($subtotal, 2),
            ];
        }

        // Calcular el margen de ganancia (precio de venta - costo de producción)
        $margenGanancia = (float) $producto->precio_sugerido - $costoTotal;

        return response()->json([
            'exito'   => true,
            'mensaje' => "Costeo del producto '{$producto->nombre}' calculado correctamente.",
            'datos'   => [
                'producto' => [
                    'id'     => $producto->id,
                    'nombre' => $producto->nombre,
                ],
                'desglose'              => $desglose,
                'costo_produccion_base' => round($costoTotal, 2),
                'precio_sugerido'       => (float) $producto->precio_sugerido,
                'margen_ganancia'       => round($margenGanancia, 2),
            ],
        ]);
    }

    /**
     * Motor de Costeo Completo: Cotización de Evento.
     *
     * Calcula el costo total de un evento incluyendo:
     * 1. Costo de insumos (costo base del producto × cantidad de invitados)
     * 2. Costo de transporte (costo por km × distancia)
     * 3. Costo de nómina (sueldo base por evento × número de empleados)
     * 4. Margen de ganancia (40% sobre el total operativo)
     *
     * Constantes de costos operativos:
     * - COSTO_POR_KM:           $4.00 MXN
     * - SUELDO_BASE_EMPLEADO:   $300.00 MXN por evento
     * - MARGEN_GANANCIA_PCT:    40%
     *
     * @param Request $request
     * @return JsonResponse
     *
     * POST /api/v1/cotizar-evento
     * Body: {
     *   "producto_id": 1,
     *   "cantidad_invitados": 50,
     *   "distancia_km": 15.5,
     *   "numero_empleados": 2
     * }
     */
    public function cotizarEvento(Request $request): JsonResponse
    {
        // ── Validación de entrada ────────────────────────────────
        $datos = $request->validate([
            'producto_id'        => 'required|integer|exists:productos,id',
            'cantidad_invitados' => 'required|integer|min:1|max:10000',
            'distancia_km'       => 'required|numeric|min:0|max:500',
            'numero_empleados'   => 'required|integer|min:1|max:50',
            'margen_ganancia_porcentaje' => 'required|numeric|min:0|max:100',
            'tarifa_por_empleado' => 'required|numeric|min:0',
            'tarifa_por_km' => 'required|numeric|min:0',
        ]);

        // ── Buscar producto con receta ───────────────────────────
        $producto = Producto::with('insumos')->find($datos['producto_id']);

        if ($producto->insumos->isEmpty()) {
            return response()->json([
                'exito'   => false,
                'mensaje' => "El producto '{$producto->nombre}' no tiene receta configurada.",
            ], 422);
        }

        $calculo = $this->calcularCotizacionEvento($producto, $datos);

        return response()->json([
            'exito'   => true,
            'mensaje' => "Cotización del evento con '{$producto->nombre}' calculada correctamente.",
            'datos'   => [
                'producto' => [
                    'id'     => $producto->id,
                    'nombre' => $producto->nombre,
                ],
                ...$calculo,
            ],
        ]);
    }

    /**
     * Generar PDF de cotización del evento.
     *
     * POST /api/v1/cotizar-evento/pdf
     */
    public function cotizarEventoPdf(Request $request)
    {
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
        ]);

        $productosIds = collect($datos['productos'])->pluck('producto_id')->unique()->values();
        $productos = Producto::with('insumos')->whereIn('id', $productosIds)->get()->keyBy('id');

        $items = [];
        $costoSnacks = 0.00;

        foreach ($datos['productos'] as $item) {
            $producto = $productos[$item['producto_id']];
            $cantidad = (float) $item['cantidad'];
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
        $costoTransporte = round($datos['tarifa_por_km'] * $datos['distancia_km'], 2);
        $costoNomina = round($datos['tarifa_por_empleado'] * $datos['numero_empleados'], 2);
        $totalOperativo = round($costoSnacks + $costoTransporte + $costoNomina, 2);

        $montoMargen = round($totalOperativo * ($datos['margen_ganancia_porcentaje'] / 100), 2);
        $totalPrecio = round($totalOperativo + $montoMargen, 2);

        $folio = 'RY-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT);

        $pdf = Pdf::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
        ])->loadView('pdf.cotizacion', [
            'folio' => $folio,
            'fecha_emision' => now()->format('d/m/Y'),
            'cliente' => [
                'nombre' => $datos['cliente_nombre'],
            ],
            'evento' => [
                'fecha' => $datos['fecha_evento'],
                'hora' => $datos['hora_evento'],
                'municipio' => $datos['municipio'],
                'colonia' => $datos['colonia'],
                'calle_numero' => $datos['calle_numero'],
                'descripcion_lugar' => $datos['descripcion_lugar'] ?? null,
            ],
            'productos' => $items,
            'totales' => [
                'snacks' => $costoSnacks,
                'logistica' => round($costoTransporte + $costoNomina, 2),
                'total' => $totalPrecio,
            ],
        ]);

        return $pdf->download('cotizacion.pdf');
    }

    private function calcularCotizacionEvento(Producto $producto, array $datos): array
    {
        $costoBaseUnitario = 0.00;
        $desgloseInsumos = [];

        foreach ($producto->insumos as $insumo) {
            $subtotal = $insumo->costo_adquisicion * $insumo->pivot->cantidad;
            $costoBaseUnitario += $subtotal;

            $desgloseInsumos[] = [
                'insumo'         => $insumo->nombre,
                'cantidad'       => (float) $insumo->pivot->cantidad,
                'unidad_medida'  => $insumo->unidad_medida,
                'costo_unitario' => (float) $insumo->costo_adquisicion,
                'subtotal'       => round($subtotal, 2),
            ];
        }

        $cantidadInvitados = $datos['cantidad_invitados'];
        $distanciaKm       = $datos['distancia_km'];
        $numEmpleados      = $datos['numero_empleados'];

        $costoInsumosTotales = round($costoBaseUnitario * $cantidadInvitados, 2);
        $costoTransporte     = round($datos['tarifa_por_km'] * $distanciaKm, 2);
        $costoNomina         = round($datos['tarifa_por_empleado'] * $numEmpleados, 2);
        $totalOperativo      = round($costoInsumosTotales + $costoTransporte + $costoNomina, 2);

        $montoMargen    = round($totalOperativo * ($datos['margen_ganancia_porcentaje'] / 100), 2);
        $precioFinal    = round($totalOperativo + $montoMargen, 2);
        $precioPorPlato = $cantidadInvitados > 0 ? round($precioFinal / $cantidadInvitados, 2) : 0;

        return [
            'parametros_evento' => [
                'cantidad_invitados' => $cantidadInvitados,
                'distancia_km'       => $distanciaKm,
                'numero_empleados'   => $numEmpleados,
            ],
            'constantes' => [
                'costo_por_km'         => $datos['tarifa_por_km'],
                'sueldo_base_empleado' => $datos['tarifa_por_empleado'],
                'margen_ganancia_pct'  => $datos['margen_ganancia_porcentaje'],
            ],
            'desglose_insumos'    => $desgloseInsumos,
            'costo_base_unitario' => round($costoBaseUnitario, 2),
            'costos_operativos'   => [
                'insumos_totales' => $costoInsumosTotales,
                'transporte'      => $costoTransporte,
                'nomina'          => $costoNomina,
            ],
            'total_operativo'  => $totalOperativo,
            'margen_ganancia'  => $montoMargen,
            'precio_final'     => $precioFinal,
            'precio_por_plato' => $precioPorPlato,
        ];
    }
}
