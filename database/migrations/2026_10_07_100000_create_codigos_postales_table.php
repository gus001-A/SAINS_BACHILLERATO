<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo Nacional de Códigos Postales (SEPOMEX / Correos de México).
 * Se llena con `php artisan cp:importar`; en producción con
 * database/sql-produccion/04_codigos_postales.sql.gz
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('codigos_postales', function (Blueprint $table) {
            $table->id();
            $table->char('cp', 5)->index();
            $table->string('colonia', 120);
            $table->string('tipo_asentamiento', 40)->nullable();
            $table->string('municipio', 120);
            $table->string('estado', 60);
            $table->string('ciudad', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_postales');
    }
};
