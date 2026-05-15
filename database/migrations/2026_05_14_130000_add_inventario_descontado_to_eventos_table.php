<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración: Añadir bandera de control de inventario descontado a eventos — Módulo D
 *
 * Garantiza la idempotencia en el descuento automatizado de insumos,
 * evitando duplicidades si un evento transiciona múltiples veces a Confirmado/Completado.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->boolean('inventario_descontado')->default(false)->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('inventario_descontado');
        });
    }
};
