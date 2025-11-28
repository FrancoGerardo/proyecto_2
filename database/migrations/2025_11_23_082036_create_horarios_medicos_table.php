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
        Schema::create('horarios_medicos', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('medico_id', 50);
            $table->string('dia_semana', 20);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->foreign('medico_id')->references('usuario_id')->on('medicos')->onDelete('cascade');
        });
        
        // Agregar constraint CHECK para PostgreSQL
        DB::statement("ALTER TABLE horarios_medicos ADD CONSTRAINT horarios_medicos_dia_semana_check CHECK (dia_semana IN ('LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'DOMINGO'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios_medicos');
    }
};
