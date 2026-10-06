<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropColumn(['respuesta_correcta', 'respuesta1', 'respuesta2']);
        });
    }

    public function down(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->string('respuesta_correcta')->nullable()->after('pregunta');
            $table->string('respuesta1')->nullable()->after('respuesta_correcta');
            $table->string('respuesta2')->nullable()->after('respuesta1');
        });
    }
};
