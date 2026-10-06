<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('tronco_id')->constrained('tronco')->onDelete('cascade');
            $table->foreignId('id_asignatura_1')->constrained('asignatura')->onDelete('cascade');
            $table->foreignId('id_asignatura_2')->nullable()->constrained('asignatura')->onDelete('set null');
            $table->foreignId('id_asignatura_3')->nullable()->constrained('asignatura')->onDelete('set null');
            $table->timestamps(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};