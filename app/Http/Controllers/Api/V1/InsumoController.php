<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador API: Insumos (Materia Prima)
 *
 * Gestiona las operaciones CRUD para los insumos a granel
 * utilizados en la producción de snacks de Raspados Yeti.
 *
 * Prefijo de rutas: /api/v1/insumos
 */
class InsumoController extends Controller
{
    /**
     * Listar todos los insumos con paginación.
     *
     * Devuelve una lista paginada de todos los insumos registrados.
     * Por defecto retorna 15 registros por página.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * GET /api/v1/insumos
     * GET /api/v1/insumos?page=2
     * GET /api/v1/insumos?per_page=25
     */
    public function index(Request $request): JsonResponse
    {
        $porPagina = $request->input('per_page', 15);

        $insumos = Insumo::orderBy('nombre')
            ->paginate($porPagina);

        return response()->json([
            'exito'   => true,
            'mensaje' => 'Lista de insumos obtenida correctamente.',
            'datos'   => $insumos,
        ]);
    }

    /**
     * Registrar un nuevo insumo.
     *
     * Valida los datos recibidos y crea un nuevo registro de materia prima.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * POST /api/v1/insumos
     * Body: { "nombre": "Jarabe de Fresa", "unidad_medida": "litros", "costo_adquisicion": 50.00 }
     */
    public function store(Request $request): JsonResponse
    {
        // Validación de datos de entrada
        $datosValidados = $request->validate([
            'nombre'             => 'required|string|max:255',
            'unidad_medida'      => 'required|string|max:50',
            'costo_adquisicion'  => 'required|numeric|min:0',
            'stock_actual'       => 'nullable|numeric|min:0',
            'stock_minimo'       => 'nullable|numeric|min:0',
            'categoria'          => 'nullable|string|max:80',
        ]);

        $insumo = Insumo::create($datosValidados);

        return response()->json([
            'exito'   => true,
            'mensaje' => "Insumo '{$insumo->nombre}' creado correctamente.",
            'datos'   => $insumo,
        ], 201);
    }

    /**
     * Mostrar un insumo específico.
     *
     * Busca un insumo por su ID e incluye los productos que lo utilizan
     * en sus recetas (relación belongsToMany).
     *
     * @param int $id
     * @return JsonResponse
     *
     * GET /api/v1/insumos/{id}
     */
    public function show(int $id): JsonResponse
    {
        $insumo = Insumo::with('productos')->find($id);

        if (!$insumo) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Insumo no encontrado.',
            ], 404);
        }

        return response()->json([
            'exito'   => true,
            'mensaje' => 'Insumo encontrado.',
            'datos'   => $insumo,
        ]);
    }

    /**
     * Actualizar un insumo existente.
     *
     * Valida los datos recibidos y actualiza el registro del insumo.
     * Todos los campos son opcionales en la actualización.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     *
     * PUT /api/v1/insumos/{id}
     * Body: { "costo_adquisicion": 55.00 }
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $insumo = Insumo::find($id);

        if (!$insumo) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Insumo no encontrado.',
            ], 404);
        }

        // Validación parcial: solo se validan los campos enviados
        $datosValidados = $request->validate([
            'nombre'             => 'sometimes|required|string|max:255',
            'unidad_medida'      => 'sometimes|required|string|max:50',
            'costo_adquisicion'  => 'sometimes|required|numeric|min:0',
            'stock_actual'       => 'sometimes|nullable|numeric|min:0',
            'stock_minimo'       => 'sometimes|nullable|numeric|min:0',
            'categoria'          => 'sometimes|nullable|string|max:80',
        ]);

        $insumo->update($datosValidados);

        return response()->json([
            'exito'   => true,
            'mensaje' => "Insumo '{$insumo->nombre}' actualizado correctamente.",
            'datos'   => $insumo,
        ]);
    }

    /**
     * Eliminar un insumo.
     *
     * Elimina el registro del insumo. Gracias al onDelete('cascade')
     * en la tabla pivote, las recetas asociadas se eliminan automáticamente.
     *
     * @param int $id
     * @return JsonResponse
     *
     * DELETE /api/v1/insumos/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $insumo = Insumo::find($id);

        if (!$insumo) {
            return response()->json([
                'exito'   => false,
                'mensaje' => 'Insumo no encontrado.',
            ], 404);
        }

        $nombreInsumo = $insumo->nombre;
        $insumo->delete();

        return response()->json([
            'exito'   => true,
            'mensaje' => "Insumo '{$nombreInsumo}' eliminado correctamente.",
        ]);
    }
}
