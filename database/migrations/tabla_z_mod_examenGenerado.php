<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up()
    {
        Schema::table('examen_realizado', function (Blueprint $table) {
            $table->json('respuestas')->nullable()->after('intento')
                  ->comment('Almacena las respuestas del estudiante en formato JSON');
        });
    }

    public function down()
    {
        Schema::table('examen_realizado', function (Blueprint $table) {
            $table->dropColumn('respuestas');
        });
    }
};