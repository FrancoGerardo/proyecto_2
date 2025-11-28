<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_cuota', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('ficha_id', 50);
            $table->integer('numero_cuotas');
            $table->decimal('monto_total', 10, 2);
            $table->decimal('monto_cuota', 10, 2);
            $table->date('fecha_inicio');
            $table->integer('intervalo_dias')->default(30);
            $table->string('estado', 20)->check("estado IN ('ACTIVO', 'PAGADO', 'MOROSO', 'CANCELADO')");
            $table->timestamps();
            
            $table->foreign('ficha_id')->references('id')->on('fichas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_cuota');
    }
};

