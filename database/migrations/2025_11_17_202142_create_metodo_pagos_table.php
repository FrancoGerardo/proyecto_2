<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('usuario_id', 50);
            $table->string('tipo', 50); // TARJETA_CREDITO, TARJETA_DEBITO, TRANSFERENCIA, EFECTIVO
            $table->string('titular', 100)->nullable();
            $table->string('numero_tarjeta_enmascarado', 50)->nullable(); // Solo últimos 4 dígitos
            $table->string('banco', 100)->nullable();
            $table->string('numero_cuenta', 50)->nullable();
            $table->json('datos_adicionales')->nullable(); // Datos encriptados o adicionales
            $table->boolean('activo')->default(true);
            $table->boolean('predeterminado')->default(false);
            $table->timestamps();
            
            $table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
            $table->index('usuario_id');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodos_pago');
    }
};
