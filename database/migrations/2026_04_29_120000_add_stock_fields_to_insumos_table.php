<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->float('stock_actual')->default(0)->after('costo_adquisicion');
            $table->float('stock_minimo')->default(0)->after('stock_actual');
            $table->string('categoria')->default('General')->after('stock_minimo');
        });
    }

    public function down(): void
    {
        Schema::table('insumos', function (Blueprint $table) {
            $table->dropColumn(['stock_actual', 'stock_minimo', 'categoria']);
        });
    }
};
