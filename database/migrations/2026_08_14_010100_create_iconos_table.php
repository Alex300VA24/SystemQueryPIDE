<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iconos', function (Blueprint $table) {
            $table->id();
            $table->string('clase', 100)->unique();
            $table->string('nombre', 100);
            $table->string('grupo', 50)->nullable();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['grupo', 'activo', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iconos');
    }
};
