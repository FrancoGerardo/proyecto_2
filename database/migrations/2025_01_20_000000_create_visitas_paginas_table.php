<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitas_paginas', function (Blueprint $table) {
            $table->id();
            $table->string('ruta');
            $table->string('nombre_pagina')->nullable();
            $table->string('usuario_id', 50)->nullable();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('set null');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('fecha_visita')->useCurrent();
            $table->timestamps();
            
            $table->index('ruta');
            $table->index('fecha_visita');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitas_paginas');
    }
};

