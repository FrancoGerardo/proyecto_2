<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('especialidades', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('nombre', 50);
            $table->string('descripcion', 256)->nullable();
            $table->string('estado', 20)->default('ACTIVA');
            $table->timestamps();
        });
        
        // Agregar constraint CHECK para PostgreSQL
        DB::statement("ALTER TABLE especialidades ADD CONSTRAINT especialidades_estado_check CHECK (estado IN ('ACTIVA', 'INACTIVA'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialidades');
    }
};
