<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fichas', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('cliente_id', 50);
            $table->string('servicio_id', 50);
            $table->string('medico_id', 50);
            $table->string('sala_id', 50)->nullable();
            $table->date('fecha');
            $table->time('hora');
            $table->string('estado', 20)->check("estado IN ('PENDIENTE', 'CONFIRMADA', 'ATENDIDA', 'CANCELADA')");
            $table->text('motivo_consulta')->nullable();
            $table->timestamps();
            
            $table->foreign('cliente_id')->references('usuario_id')->on('clientes')->onDelete('cascade');
            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('cascade');
            $table->foreign('medico_id')->references('usuario_id')->on('medicos')->onDelete('cascade');
            $table->foreign('sala_id')->references('id')->on('salas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fichas');
    }
};

