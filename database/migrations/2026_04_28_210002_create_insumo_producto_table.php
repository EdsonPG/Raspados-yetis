<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Tabla Pivote de Recetas (insumo_producto)
 *
 * Tabla intermedia que relaciona Insumos con Productos, conformando
 * la "receta" de cada producto. Registra la cantidad fraccionada exacta
 * de cada insumo necesario para producir una unidad del producto.
 *
 * Ejemplo: Para un "Trole de 8oz" se necesitan:
 *   - 0.25 litros de Jarabe de Fresa
 *   - 0.15 kg de Hielo Triturado
 *   - 1 pieza de Vaso 8oz
 *
 * La convención de nombres de Laravel para tablas pivote es ordenar
 * los nombres de los modelos alfabéticamente en singular: insumo_producto
 */
return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::create('insumo_producto', function (Blueprint $table) {
            // Identificador único autoincremental
            $table->id();

            // Clave foránea al insumo (materia prima)
            $table->foreignId('insumo_id')
                  ->constrained('insumos')
                  ->onDelete('cascade');

            // Clave foránea al producto (snack final)
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('cascade');

            // Cantidad fraccionada exacta del insumo requerida para una unidad del producto
            // Se usa decimal con 10 dígitos totales y 4 decimales para permitir
            // fracciones precisas (ej. 0.0625 litros = 62.5 ml)
            $table->decimal('cantidad', 10, 4);

            // Marcas de tiempo: created_at y updated_at
            $table->timestamps();

            // Índice único compuesto para evitar duplicados en la receta
            // Un insumo solo puede aparecer una vez por producto
            $table->unique(['insumo_id', 'producto_id']);
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('insumo_producto');
    }
};
