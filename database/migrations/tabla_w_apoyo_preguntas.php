<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apoyo_preguntas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examen') 
                  ->constrained('examen_generado') 
                  ->onDelete('cascade');
            $table->foreignId('pregunta')->constrained('preguntas')->onDelete('cascade');
            $table->timestamps(false);
            
            $table->unique(['examen', 'pregunta']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apoyo_preguntas');
    }
};