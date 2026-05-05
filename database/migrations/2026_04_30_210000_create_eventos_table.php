<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla de Eventos — Módulo B
 *
 * Almacena los eventos programados de Raspados Yeti.
 * Cada evento representa un servicio contratado para una fecha y lugar,
 * con un flujo de estados: Cotizado → Anticipo Pagado → Confirmado → Completado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();

            // ── Datos del cliente ────────────────────────────────────────
            $table->string('cliente_nombre', 255);
            $table->string('cliente_telefono', 20)->nullable();

            // ── Fecha y horario del evento ───────────────────────────────
            $table->date('fecha_evento');
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();

            // ── Ubicación (desglosada, convención de cotizaciones) ───────
            $table->string('municipio', 120);
            $table->string('colonia', 120);
            $table->string('calle_numero', 160);
            $table->string('descripcion_lugar', 500)->nullable();

            // ── Paquete contratado ───────────────────────────────────────
            $table->string('paquete_contratado', 255);
            $table->integer('total_invitados')->default(0);
            $table->decimal('total_precio', 10, 2)->default(0.00);

            // ── Flujo de estados ─────────────────────────────────────────
            $table->enum('estado', [
                'Cotizado',
                'Anticipo Pagado',
                'Confirmado',
                'Completado',
            ])->default('Cotizado');

            // ── Notas internas ───────────────────────────────────────────
            $table->text('notas')->nullable();

            // ── Relación opcional con cotización origen ──────────────────
            $table->foreignId('cotizacion_id')
                  ->nullable()
                  ->constrained('cotizaciones')
                  ->nullOnDelete();

            $table->timestamps();

            // ── Índices ──────────────────────────────────────────────────
            $table->index('fecha_evento');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
