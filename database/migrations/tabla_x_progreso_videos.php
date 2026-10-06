<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progreso_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiante')->onDelete('cascade');
            $table->foreignId('video_id')->constrained('videos')->onDelete('cascade');
            $table->datetime('fecha_visto')->nullable();
            $table->boolean('completado')->default(false);
            $table->string('ultimo_segundo', 20)->nullable()->comment('Ej: 00:02:35');
            $table->integer('veces_visto')->default(1);
            $table->timestamps(false);
            
            $table->unique(['estudiante_id', 'video_id']);
            $table->index('completado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progreso_videos');
    }
};