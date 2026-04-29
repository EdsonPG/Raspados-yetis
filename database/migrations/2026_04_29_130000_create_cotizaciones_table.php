<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('cliente_nombre');
            $table->date('fecha_evento');
            $table->time('hora_evento');
            $table->string('direccion');
            $table->integer('total_invitados');
            $table->decimal('total_precio', 10, 2);
            $table->enum('estado', ['Pendiente', 'Confirmado', 'Cancelado'])->default('Pendiente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
