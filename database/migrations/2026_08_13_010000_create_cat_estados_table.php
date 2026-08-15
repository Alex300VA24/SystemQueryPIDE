<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_estado', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('descripcion', 100);
            $table->enum('aplicable_a', ['GENERAL', 'USUARIO'])->default('GENERAL');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cat_estado');
    }
};
