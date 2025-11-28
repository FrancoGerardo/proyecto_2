<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('persona_id', 50)->unique();
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password_hash', 255);
            $table->string('foto_url', 255)->nullable();
            $table->string('tipo_usuario', 20)->check("tipo_usuario IN ('PROPIETARIO', 'SECRETARIA', 'MEDICO', 'CLIENTE')");
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_registro')->useCurrent();
            $table->rememberToken();
            $table->string('current_team_id', 50)->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->timestamps();
            
            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('cascade');
            $table->index('tipo_usuario');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};

