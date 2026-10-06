<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examen_generado', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_preguntas');
            $table->integer('tiempo')->comment('Tiempo límite del examen en minutos');
            $table->string('tipo_examen');
            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examen_generado');
    }
};