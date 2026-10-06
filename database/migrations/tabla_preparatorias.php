<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preparatorias', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 100);
            $table->string('municipio', 100);
            $table->string('localidad', 100);
            $table->string('ambito')->nullable();
            $table->string('tipo');
            $table->string('servicio', 100)->nullable();
            $table->string('clave', 50)->unique();
            $table->string('turno')->nullable();
            $table->string('centro_educativo');
            $table->text('direccion')->nullable();
            $table->timestamps(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preparatorias');
    }
};