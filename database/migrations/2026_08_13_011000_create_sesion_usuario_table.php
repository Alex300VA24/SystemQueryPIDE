<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesion_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('token', 255)->unique();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('fecha_inicio')->useCurrent();
            $table->timestamp('fecha_expiracion');
            $table->timestamp('fecha_cierre')->nullable();
            $table->boolean('activo')->default(true);

            $table->index(['usuario_id', 'activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesion_usuario');
    }
};
