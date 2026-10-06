<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiempo_estudio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('estudiante_id');
            $table->date('fecha');
            $table->integer('segundos_estudiados')->default(0);
            $table->integer('minutos_estudiados')->default(0);
            $table->integer('horas_estudiadas')->default(0);
            $table->integer('sesiones')->default(0);
            $table->timestamp('ultima_actividad')->nullable();
            $table->timestamps(); 
            
            // Llaves foráneas
            $table->foreign('estudiante_id')
                  ->references('id')
                  ->on('estudiante')
                  ->onDelete('cascade');
            
            // Índices y únicos
            $table->unique(['estudiante_id', 'fecha']);
            $table->index(['estudiante_id', 'fecha']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiempo_estudio');
    }
};