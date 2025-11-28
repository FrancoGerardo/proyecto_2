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
        Schema::create('medico_especialidad', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('medico_id', 50);
            $table->string('especialidad_id', 50);
            $table->timestamps();
            
            $table->foreign('medico_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->foreign('especialidad_id')->references('id')->on('especialidades')->onDelete('cascade');
            $table->unique(['medico_id', 'especialidad_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medico_especialidad');
    }
};
