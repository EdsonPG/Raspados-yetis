<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn('direccion');
            $table->string('municipio')->after('hora_evento');
            $table->string('colonia')->after('municipio');
            $table->string('calle_numero')->after('colonia');
            $table->text('descripcion_lugar')->nullable()->after('calle_numero');
        });
    }

    public function down(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropColumn(['municipio', 'colonia', 'calle_numero', 'descripcion_lugar']);
            $table->string('direccion')->after('hora_evento');
        });
    }
};
