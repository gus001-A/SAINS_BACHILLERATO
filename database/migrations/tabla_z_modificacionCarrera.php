<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->decimal('calificacion_minima', 5, 2)->nullable()->after('id_asignatura_3')
                  ->comment('Calificación mínima requerida para acceder a esta carrera (ej: 85.00)');
        });
    }

    public function down()
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->dropColumn('calificacion_minima');
        });
    }
};