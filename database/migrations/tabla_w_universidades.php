<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universidades', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 100);
            $table->string('municipio', 100);
            $table->string('localidad', 100);
            $table->foreignId('carrera_id')->constrained('carreras')->onDelete('cascade');
            $table->string('duracion');
            $table->string('tipo')->default('Pública');
            $table->string('clave', 50)->unique();
            $table->text('direccion')->nullable();
            $table->timestamps(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universidades');
    }
};