<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('plan_cuota_id', 50)->nullable();
            $table->string('ficha_id', 50);
            $table->decimal('monto', 10, 2);
            $table->string('tipo', 20)->check("tipo IN ('CONTADO', 'CUOTA', 'ABONO')");
            $table->integer('numero_cuota')->nullable();
            $table->timestamp('fecha_pago')->useCurrent();
            $table->string('metodo_pago', 20)->check("metodo_pago IN ('EFECTIVO', 'TARJETA', 'TRANSFERENCIA')");
            $table->string('comprobante_url', 255)->nullable();
            $table->string('estado', 20)->check("estado IN ('PENDIENTE', 'PAGADO', 'ANULADO')");
            $table->timestamps();
            
            $table->foreign('plan_cuota_id')->references('id')->on('planes_cuota')->onDelete('set null');
            $table->foreign('ficha_id')->references('id')->on('fichas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};

