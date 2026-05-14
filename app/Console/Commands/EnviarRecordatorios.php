<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Evento;
use App\Services\NotificacionService;

class EnviarRecordatorios extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:recordatorios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera alertas y notificaciones a clientes con eventos próximos (Recordatorios Módulo C)';

    /**
     * Execute the console command.
     */
    public function handle(NotificacionService $notificacionService)
    {
        $this->info('Iniciando sistema de recordatorios (Módulo C)...');

        $eventosProximos = Evento::with('cliente')
            ->whereBetween('fecha_evento', [now()->toDateString(), now()->addDays(3)->toDateString()])
            ->whereIn('estado', ['Confirmado', 'Anticipo Pagado'])
            ->get();

        if ($eventosProximos->isEmpty()) {
            $this->info('No hay eventos próximos en los siguientes 3 días que requieran recordatorio.');
            return;
        }

        foreach ($eventosProximos as $evento) {
            // Simulamos el envío de WhatsApp / Email
            $this->line("Notificando al cliente: {$evento->cliente_nombre} (Tel: {$evento->cliente_telefono}) para el evento del {$evento->fecha_evento}.");
            $notificacionService->recordatorioEvento($evento);
        }

        $this->info('Recordatorios enviados con éxito.');
    }
}
