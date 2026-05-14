<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->integer('limit', 10);
        $limit = max(1, min($limit, 50));

        $notificaciones = Notificacion::query()
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Notificaciones obtenidas correctamente.',
            'datos' => [
                'notificaciones' => $notificaciones,
                'no_leidas' => Notificacion::noLeidas()->count(),
            ],
        ]);
    }

    public function marcarLeida(int $id): JsonResponse
    {
        $notificacion = Notificacion::find($id);

        if (!$notificacion) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Notificación no encontrada.',
            ], 404);
        }

        $notificacion->marcarLeida();

        return response()->json([
            'exito' => true,
            'mensaje' => 'Notificación marcada como leída.',
            'datos' => $notificacion,
        ]);
    }

    public function marcarTodas(): JsonResponse
    {
        Notificacion::noLeidas()->update(['leida_at' => now()]);

        return response()->json([
            'exito' => true,
            'mensaje' => 'Todas las notificaciones fueron marcadas como leídas.',
        ]);
    }
}
