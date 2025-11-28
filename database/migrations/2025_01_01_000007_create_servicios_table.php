<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('categoria', 20)->check("categoria IN ('INTERNACION', 'ESPECIALIDAD', 'ENFERMERIA')");
            $table->decimal('costo', 10, 2);
            $table->integer('duracion_minutos')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicios');
    }
};

