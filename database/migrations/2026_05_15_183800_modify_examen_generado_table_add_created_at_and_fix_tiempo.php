<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Verificar si la tabla existe
        if (Schema::hasTable('Examen_generado')) {
            
            // 1. Agregar created_at y updated_at si no existen
            if (!Schema::hasColumn('Examen_generado', 'created_at')) {
                Schema::table('Examen_generado', function (Blueprint $table) {
                    $table->timestamp('created_at')->nullable()->after('fecha_creacion');
                });
            }
            
            if (!Schema::hasColumn('Examen_generado', 'updated_at')) {
                Schema::table('Examen_generado', function (Blueprint $table) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                });
            }
            
            // 2. Cambiar tipo de 'tiempo' a integer
            if (Schema::hasColumn('Examen_generado', 'tiempo')) {
                Schema::table('Examen_generado', function (Blueprint $table) {
                    $table->integer('tiempo')->change();
                });
            }
            
            // 3. Copiar datos de fecha_creacion a created_at si existe
            if (Schema::hasColumn('Examen_generado', 'fecha_creacion')) {
                \DB::statement('UPDATE Examen_generado SET created_at = fecha_creacion WHERE created_at IS NULL AND fecha_creacion IS NOT NULL');
            }
            
            // 4. Si hay registros sin created_at, poner fecha actual
            \DB::statement('UPDATE Examen_generado SET created_at = NOW() WHERE created_at IS NULL');
        }
    }

    public function down()
    {
        Schema::table('Examen_generado', function (Blueprint $table) {
            // No revertimos cambios para proteger datos
        });
    }
};