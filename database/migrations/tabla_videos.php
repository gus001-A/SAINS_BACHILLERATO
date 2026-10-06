<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('eje', 100)->nullable();
            $table->string('materia');
            $table->string('tema');
            $table->string('titulo');
            $table->string('link');
            $table->time('duracion');
            $table->boolean('plan')->default(false)->comment('0=Plan normal, 1=Plan premium');
            $table->timestamps(false);
            
            $table->index(['materia', 'tema']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};