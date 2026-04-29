<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla de Productos (Snacks Finales)
 *
 * Registra los productos terminados que se venden al cliente final.
 * Cada producto tiene un nombre descriptivo y un precio sugerido
 * que puede calcularse automáticamente a partir de su receta (costos de insumos).
 *
 * Ejemplos de productos: "Trole de 8oz", "Raspa de Fresa", "Yeti Especial 16oz"
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            // Identificador único autoincremental
            $table->id();

            // Nombre del producto final (ej. "Trole de 8oz", "Raspa de Fresa")
            $table->string('nombre');

            // Precio sugerido/calculado de venta al público
            // Este valor puede ser calculado automáticamente sumando los costos
            // de los insumos según la receta, o establecido manualmente
            // Se usa decimal con 10 dígitos totales y 2 decimales para precisión monetaria
            $table->decimal('precio_sugerido', 10, 2)->default(0.00);

            // Marcas de tiempo: created_at y updated_at
            $table->timestamps();
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
