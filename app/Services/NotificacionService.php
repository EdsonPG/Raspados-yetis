<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\Evento;
use App\Models\Notificacion;

class NotificacionService
{
    public function crear(string $tipo, string $titulo, string $mensaje, ?string $accionUrl = null, array $contexto = []): Notificacion
    {
        return Notificacion::create([
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'accion_url' => $accionUrl,
            'contexto' => $contexto ?: null,
        ]);
    }

    public function cotizacionAprobada(Cotizacion $cotizacion, Evento $evento): Notificacion
    {
        return $this->crear(
            'cotizacion_aprobada',
            'Cotización aprobada',
            "La cotización #{$cotizacion->id} fue aprobada y se creó el evento para {$cotizacion->cliente_nombre}.",
            '/eventos',
            [
                'cotizacion_id' => $cotizacion->id,
                'evento_id' => $evento->id,
                'cliente_nombre' => $cotizacion->cliente_nombre,
            ]
        );
    }

    public function cotizacionCancelada(Cotizacion $cotizacion): Notificacion
    {
        return $this->crear(
            'cotizacion_cancelada',
            'Cotización cancelada',
            "La cotización #{$cotizacion->id} fue descartada y no se enviará a Agenda.",
            '/historial',
            [
                'cotizacion_id' => $cotizacion->id,
                'cliente_nombre' => $cotizacion->cliente_nombre,
            ]
        );
    }

    public function recordatorioEvento(Evento $evento): Notificacion
    {
        return $this->crear(
            'recordatorio_evento',
            'Evento próximo',
            "El evento de {$evento->cliente_nombre} está programado para {$evento->fecha_evento->format('d/m/Y')}.",
            '/eventos',
            [
                'evento_id' => $evento->id,
                'cliente_nombre' => $evento->cliente_nombre,
                'fecha_evento' => $evento->fecha_evento?->toDateString(),
            ]
        );
    }
}
