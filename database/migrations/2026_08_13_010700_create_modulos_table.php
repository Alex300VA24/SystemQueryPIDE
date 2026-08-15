<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sistema_id')->constrained('sistemas')->cascadeOnDelete();
            $table->foreignId('padre_id')->nullable()->constrained('modulos')->nullOnDelete();
            $table->string('codigo', 50);
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('url', 255)->nullable();
            $table->string('icono', 100)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->unsignedTinyInteger('nivel')->default(1);
            $table->boolean('es_menu')->default(true);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['sistema_id', 'codigo']);
            $table->index(['padre_id', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};
