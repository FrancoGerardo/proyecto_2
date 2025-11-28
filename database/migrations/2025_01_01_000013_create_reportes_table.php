<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('tipo', 20)->check("tipo IN ('FINANCIERO', 'CLINICO', 'OPERATIVO')");
            $table->string('nombre', 100);
            $table->json('parametros')->nullable();
            $table->string('url_archivo', 255)->nullable();
            $table->string('estado', 20)->check("estado IN ('GENERANDO', 'COMPLETADO', 'ERROR')");
            $table->string('usuario_generador', 50);
            $table->timestamps();
            
            $table->foreign('usuario_generador')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};

