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
        // Eliminar la FK existente que referencia a Medicos
        Schema::table('medico_especialidad', function (Blueprint $table) {
            $table->dropForeign(['medico_id']);
        });

        // Agregar nueva FK que referencia a Usuarios(id)
        Schema::table('medico_especialidad', function (Blueprint $table) {
            $table->foreign('medico_id')->references('id')->on('usuarios')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir: eliminar FK de Usuarios
        Schema::table('medico_especialidad', function (Blueprint $table) {
            $table->dropForeign(['medico_id']);
        });

        // Restaurar FK original a Medicos
        Schema::table('medico_especialidad', function (Blueprint $table) {
            $table->foreign('medico_id')->references('usuario_id')->on('medicos')->onDelete('cascade');
        });
    }
};
