<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examen_realizado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante')->constrained('estudiante')->onDelete('cascade');
            $table->datetime('fecha_inicio');
            $table->time('hora_inicio');
            $table->date('fecha_fin')->nullable();
            $table->time('hora_fin')->nullable();
            $table->time('tiempo')->nullable()->comment('Tiempo que tardó en resolverlo');
            $table->decimal('calificacion', 5, 2)->nullable();
            $table->foreignId('examen')->constrained('examen_generado')->onDelete('cascade');
            $table->integer('intento')->default(1);
            $table->timestamps(false);
            
            $table->unique(['estudiante', 'examen', 'intento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examen_realizado');
    }
};