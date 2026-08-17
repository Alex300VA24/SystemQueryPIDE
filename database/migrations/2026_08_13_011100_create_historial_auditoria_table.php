<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_auditoria', function (Blueprint $table) {
            $table->id();
            $table->string('tabla', 50);
            $table->unsignedBigInteger('registro_id');
            $table->string('operacion', 10);
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->timestamp('fecha')->useCurrent();
            $table->string('ip', 45)->nullable();
            $table->json('datos_anteriores')->nullable();
            $table->json('datos_nuevos')->nullable();
            $table->text('observacion')->nullable();

            $table->index(['tabla', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_auditoria');
    }
};
