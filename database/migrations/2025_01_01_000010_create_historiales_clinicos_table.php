<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historiales_clinicos', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('cliente_id', 50)->unique();
            $table->text('alergias')->nullable();
            $table->text('enfermedades_cronicas')->nullable();
            $table->text('medicamentos_habituales')->nullable();
            $table->timestamps();
            
            $table->foreign('cliente_id')->references('usuario_id')->on('clientes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historiales_clinicos');
    }
};

