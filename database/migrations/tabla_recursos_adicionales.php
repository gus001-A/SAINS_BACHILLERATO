<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos_adicionales', function (Blueprint $table) {
            $table->id();
            $table->string('materia_nombre');
            $table->string('tema');
            $table->text('descripcion')->nullable();
            $table->string('link');
            $table->timestamps(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recursos_adicionales');
    }
};