<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla de Insumos (Materia Prima)
 *
 * Registra toda la materia prima a granel utilizada en la producción
 * de los snacks de Raspados Yeti. Cada insumo tiene un nombre,
 * una unidad de medida y su costo de adquisición unitario.
 *
 * Ejemplos de insumos: Jarabe de fresa (litros), Hielo (kg), Vasos 8oz (piezas)
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('insumos', function (Blueprint $table) {
            // Identificador único autoincremental
            $table->id();

            // Nombre descriptivo del insumo (ej. "Jarabe de Fresa", "Hielo Triturado")
            $table->string('nombre');

            // Unidad de medida para el insumo (ej. "litros", "kg", "piezas", "ml")
            $table->string('unidad_medida');

            // Costo de adquisición por unidad de medida (precio por litro, por kg, etc.)
            // Se usa decimal con 10 dígitos totales y 2 decimales para precisión monetaria
            $table->decimal('costo_adquisicion', 10, 2);

            // Marcas de tiempo: created_at y updated_at
            $table->timestamps();
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumos');
    }
};
