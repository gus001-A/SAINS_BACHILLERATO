<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estudiante', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('paterno');
            $table->string('materno')->nullable();
            $table->date('fecha_nacimiento');
            $table->string('sexo')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('telefono_casa', 20)->nullable();
            
            // Foreing keys
            $table->foreignId('escuela_procedencia')->nullable()->constrained('preparatorias')->nullOnDelete();
            $table->string('cupon', 50)->nullable();
            $table->date('fecha_inscripcion');
            $table->boolean('plan_activo')->default(false);
            $table->foreignId('universidad_interes')->nullable()->constrained('universidades')->nullOnDelete();
            $table->string('foto')->nullable();
            $table->foreignId('usuario')->unique()->constrained('usuario')->onDelete('cascade');
            
            $table->timestamps(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estudiante');
    }
};