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
        Schema::create('medico_servicios', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('medico_id', 50);
            $table->string('servicio_id', 50);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->foreign('medico_id')->references('usuario_id')->on('medicos')->onDelete('cascade');
            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('cascade');
            $table->unique(['medico_id', 'servicio_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medico_servicios');
    }
};
