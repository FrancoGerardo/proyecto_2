<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('ficha_id', 50);
            $table->string('tipo', 20)->check("tipo IN ('TRIAGE', 'CONSULTA', 'TRATAMIENTO')");
            $table->timestamp('fecha')->useCurrent();
            $table->json('signos_vitales')->nullable();
            $table->text('motivo_consulta')->nullable();
            $table->string('nivel_urgencia', 20)->check("nivel_urgencia IN ('BAJA', 'MEDIA', 'ALTA', 'URGENTE')")->nullable();
            $table->text('diagnostico')->nullable();
            $table->text('observaciones')->nullable();
            $table->text('tratamiento_prescrito')->nullable();
            $table->json('medicamentos')->nullable();
            $table->timestamps();
            
            $table->foreign('ficha_id')->references('id')->on('fichas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};

