<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('tabla_afectada', 50);
            $table->string('registro_id', 50);
            $table->string('accion', 10)->check("accion IN ('INSERT', 'UPDATE', 'DELETE')");
            $table->string('usuario_id', 50)->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->timestamp('fecha')->useCurrent();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->index(['tabla_afectada', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};

