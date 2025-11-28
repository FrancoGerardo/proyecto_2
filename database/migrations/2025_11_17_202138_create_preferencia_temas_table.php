<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preferencias_tema', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('usuario_id', 50);
            $table->string('tema', 20)->default('adultos'); // ninos, jovenes, adultos
            $table->string('modo', 20)->default('dia'); // dia, noche, auto
            $table->string('tamaño_fuente', 20)->default('normal'); // pequeño, normal, grande, muy-grande
            $table->string('contraste', 20)->default('normal'); // normal, alto
            $table->boolean('modo_auto')->default(false);
            $table->timestamps();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->unique('usuario_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preferencias_tema');
    }
};
