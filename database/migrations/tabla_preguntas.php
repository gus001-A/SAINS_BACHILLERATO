<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_area')->constrained('area_preguntas')->onDelete('cascade');
            $table->text('pregunta');
            $table->string('respuesta_correcta');
            $table->string('respuesta1');
            $table->string('respuesta2');
            $table->timestamps(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas');
    }
};