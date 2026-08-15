<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol_modulo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('sistema_id')->constrained('sistemas')->cascadeOnDelete();
            $table->foreignId('modulo_id')->constrained('modulos')->cascadeOnDelete();
            $table->timestamp('fecha_asignacion')->useCurrent();

            $table->unique(['rol_id', 'modulo_id']);
            $table->index(['sistema_id', 'modulo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_modulo');
    }
};
