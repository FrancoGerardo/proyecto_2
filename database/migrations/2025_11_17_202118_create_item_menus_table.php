<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_menu', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('nombre', 100);
            $table->string('ruta', 255);
            $table->string('icono', 100)->nullable();
            $table->integer('orden')->default(0);
            $table->string('permiso_requerido', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->string('item_padre_id', 50)->nullable();
            $table->timestamps();
            
            $table->index('orden');
            $table->index('activo');
        });

        // Crear foreign key después de crear la tabla (para PostgreSQL)
        Schema::table('items_menu', function (Blueprint $table) {
            $table->foreign('item_padre_id')->references('id')->on('items_menu')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_menu');
    }
};
