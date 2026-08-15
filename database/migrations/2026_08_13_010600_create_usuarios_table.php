<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->restrictOnDelete();
            $table->string('username', 50)->unique();
            $table->string('password_hash', 255);
            $table->string('email', 100)->nullable()->unique();
            $table->string('telefono', 20)->nullable();
            $table->boolean('requiere_cambio_password')->default(false);
            $table->unsignedTinyInteger('intentos_fallidos')->default(0);
            $table->timestamp('fecha_ultimo_acceso')->nullable();
            $table->foreignId('estado_id')->constrained('cat_estado')->restrictOnDelete();
            $table->timestamp('fecha_actualizacion_password')->nullable();
            $table->char('cui', 1);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
