<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    /**
     * Listar clientes.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('per_page', 15);
        $clientes = Cliente::withCount(['eventos', 'cotizaciones'])
            ->orderBy('nombre', 'asc')
            ->paginate($limit);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Listado de clientes obtenido correctamente.',
            'datos' => $clientes
        ]);
    }

    /**
     * Crear un nuevo cliente.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'notas' => 'nullable|string|max:1000',
            'direccion_default' => 'nullable|string|max:500',
        ]);

        $cliente = Cliente::create($validated);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Cliente creado exitosamente.',
            'datos' => $cliente
        ], 201);
    }

    /**
     * Ver un cliente específico.
     */
    public function show($id): JsonResponse
    {
        $cliente = Cliente::with(['eventos', 'cotizaciones'])->find($id);

        if (!$cliente) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Cliente no encontrado.'
            ], 404);
        }

        return response()->json([
            'exito' => true,
            'mensaje' => 'Cliente obtenido correctamente.',
            'datos' => $cliente
        ]);
    }

    /**
     * Actualizar cliente.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Cliente no encontrado.'
            ], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'notas' => 'nullable|string|max:1000',
            'direccion_default' => 'nullable|string|max:500',
        ]);

        $cliente->update($validated);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Cliente actualizado exitosamente.',
            'datos' => $cliente
        ]);
    }

    /**
     * Eliminar cliente.
     */
    public function destroy($id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Cliente no encontrado.'
            ], 404);
        }

        // Prevenimos eliminar si tiene eventos asociados
        if ($cliente->eventos()->count() > 0) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'No se puede eliminar el cliente porque tiene eventos asociados.'
            ], 400);
        }

        $cliente->delete();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Cliente eliminado correctamente.'
        ]);
    }
}
