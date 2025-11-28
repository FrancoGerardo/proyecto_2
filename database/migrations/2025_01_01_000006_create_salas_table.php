<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salas', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('numero', 20)->unique();
            $table->string('categoria', 50);
            $table->text('equipamiento')->nullable();
            $table->string('estado', 20)->check("estado IN ('DISPONIBLE', 'OCUPADA', 'MANTENIMIENTO', 'INACTIVA')");
            $table->integer('capacidad')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
};
