<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ISSFAM: carreras del bachillerato tecnológico, guías por carrera,
 * CURP + domicilio del estudiante y candado de ediciones de datos.
 *
 * Equivalente para producción: database/sql-produccion/03_issfam_carreras_guias.sql
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carreras_bachillerato', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120)->unique();
            $table->text('descripcion')->nullable();
            $table->string('icono', 40)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        $ahora = now();
        DB::table('carreras_bachillerato')->insert([
            ['nombre' => 'Informática Administrativa', 'icono' => 'fa-laptop-code', 'orden' => 1,
             'descripcion' => 'Uso de herramientas informáticas para la gestión y el control administrativo de las organizaciones.'],
            ['nombre' => 'Administración de Recursos Humanos', 'icono' => 'fa-people-group', 'orden' => 2,
             'descripcion' => 'Reclutamiento, capacitación y desarrollo del personal dentro de las organizaciones.'],
            ['nombre' => 'Administración', 'icono' => 'fa-chart-column', 'orden' => 3,
             'descripcion' => 'Planeación, organización y control de los recursos de una empresa o institución.'],
            ['nombre' => 'Programador', 'icono' => 'fa-code', 'orden' => 4,
             'descripcion' => 'Desarrollo de programas y aplicaciones con lógica de programación y buenas prácticas.'],
        ]);
        DB::table('carreras_bachillerato')->update(['activa' => true, 'created_at' => $ahora, 'updated_at' => $ahora]);

        Schema::create('guias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras_bachillerato')->cascadeOnDelete();
            $table->string('titulo', 180);
            $table->text('descripcion')->nullable();
            $table->string('archivo')->nullable();
            $table->string('enlace', 500)->nullable();
            $table->unsignedInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });

        Schema::table('estudiante', function (Blueprint $table) {
            $table->string('curp', 18)->nullable();
            $table->string('calle_numero', 150)->nullable();
            $table->string('colonia', 120)->nullable();
            $table->string('codigo_postal', 5)->nullable();
            $table->string('municipio', 120)->nullable();
            $table->string('entidad_federativa', 60)->nullable();
            $table->foreignId('carrera_id')->nullable()->constrained('carreras_bachillerato')->nullOnDelete();
            $table->unsignedSmallInteger('ediciones_total')->default(0);
            $table->unsignedSmallInteger('ediciones_dia')->default(0);
            $table->date('ediciones_fecha')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('estudiante', function (Blueprint $table) {
            $table->dropConstrainedForeignId('carrera_id');
            $table->dropColumn([
                'curp', 'calle_numero', 'colonia', 'codigo_postal', 'municipio', 'entidad_federativa',
                'ediciones_total', 'ediciones_dia', 'ediciones_fecha',
            ]);
        });
        Schema::dropIfExists('guias');
        Schema::dropIfExists('carreras_bachillerato');
    }
};
