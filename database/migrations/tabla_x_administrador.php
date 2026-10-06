<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('administradores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->string('nombre', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('sexo')->nullable();
            $table->timestamps();
            
            // Foreign key
            $table->foreign('usuario_id')->references('id')->on('usuario')->onDelete('cascade');
            
            // Índices
            $table->index('usuario_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('administradores');
    }
};