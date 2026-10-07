<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Guías de tronco común: `carrera_id` NULL = la ven todos los estudiantes.
 * (En producción ya va incluido en database/sql-produccion/03_issfam_carreras_guias.sql.)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE guias MODIFY carrera_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::table('guias')->whereNull('carrera_id')->delete();
        DB::statement('ALTER TABLE guias MODIFY carrera_id BIGINT UNSIGNED NOT NULL');
    }
};
