<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pregunta_opciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pregunta_id')->constrained('preguntas')->onDelete('cascade');
            $table->string('texto');
            $table->boolean('es_correcta')->default(false);
            $table->unsignedTinyInteger('orden')->default(1);
        });

        // Migra las ~3 opciones fijas de cada pregunta existente a la nueva tabla,
        // antes de que una migración posterior elimine esas columnas.
        if (Schema::hasColumn('preguntas', 'respuesta_correcta')) {
            $preguntas = DB::table('preguntas')->select('id', 'respuesta_correcta', 'respuesta1', 'respuesta2')->get();

            $filas = [];
            foreach ($preguntas as $p) {
                $filas[] = ['pregunta_id' => $p->id, 'texto' => $p->respuesta_correcta, 'es_correcta' => true, 'orden' => 1];
                $filas[] = ['pregunta_id' => $p->id, 'texto' => $p->respuesta1, 'es_correcta' => false, 'orden' => 2];
                $filas[] = ['pregunta_id' => $p->id, 'texto' => $p->respuesta2, 'es_correcta' => false, 'orden' => 3];
            }

            foreach (array_chunk($filas, 300) as $lote) {
                DB::table('pregunta_opciones')->insert($lote);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pregunta_opciones');
    }
};
