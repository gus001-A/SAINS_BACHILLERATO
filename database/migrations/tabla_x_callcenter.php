<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('interacciones_call_center')) {
            Schema::create('interacciones_call_center', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_usuario_contacta'); 
                $table->unsignedBigInteger('id_estudiante'); 
                $table->date('fecha_contacto');
                $table->time('hora_contacto');
                $table->text('nota'); 
                $table->string('tipo_contacto', 50)->default('llamada');
                $table->string('estado_seguimiento', 50)->default('pendiente');
                $table->dateTime('proximo_contacto')->nullable();
                $table->string('motivo_contacto', 255);
                $table->string('resultado', 255)->default('no_contesto');
                $table->timestamps();
                $table->softDeletes(); 

                // Llaves foráneas
                $table->foreign('id_usuario_contacta', 'fk_contacta_usuario')
                      ->references('id')->on('usuario')->onDelete('cascade');
                $table->foreign('id_estudiante', 'fk_estudiante_usuario')
                      ->references('id')->on('usuario')->onDelete('cascade');

                // Índices con nombres cortos
                $table->index(['fecha_contacto', 'estado_seguimiento'], 'idx_fecha_estado');
                $table->index('id_estudiante', 'idx_estudiante');
                $table->index('id_usuario_contacta', 'idx_contacta');
                $table->index('fecha_contacto', 'idx_fecha');
                $table->index('estado_seguimiento', 'idx_estado');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('interacciones_call_center');
    }
};