<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notificaciones')) {
            return;
        }

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario')->index();
            $table->string('tipo', 40)->default('info');
            $table->string('titulo', 180);
            $table->text('mensaje');
            $table->string('url', 255)->nullable();
            $table->string('icono', 30)->default('bell');
            $table->string('color', 20)->default('indigo');
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('usuario')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
