<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_tipo_id')->constrained('tipo_documento')->restrictOnDelete();
            $table->string('documento_numero', 20);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->string('nombres', 100);
            $table->date('fecha_nacimiento')->nullable();
            $table->char('sexo', 1)->nullable();
            $table->string('telefono_fijo', 20)->nullable();
            $table->string('telefono_movil', 20)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('via_nombre', 100)->nullable();
            $table->string('via_numero', 20)->nullable();
            $table->string('via_mz', 10)->nullable();
            $table->string('via_lote', 10)->nullable();
            $table->string('foto_url', 255)->nullable();
            $table->foreignId('estado_id')->constrained('cat_estado')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['documento_tipo_id', 'documento_numero']);
            $table->index(['apellido_paterno', 'apellido_materno', 'nombres']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
