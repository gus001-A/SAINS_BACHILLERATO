<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asignatura')->constrained('asignatura')->onDelete('cascade');
            $table->integer('num_clase');
            $table->string('nombre_clase');
            $table->string('link')->nullable();
            $table->string('url')->nullable();
            $table->timestamps(false);
            
            $table->unique(['id_asignatura', 'num_clase']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clases');
    }
};