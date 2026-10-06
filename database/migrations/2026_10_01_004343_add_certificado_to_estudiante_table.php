<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estudiante', function (Blueprint $table) {
            $table->string('certificado_path')->nullable()->after('foto');
            $table->timestamp('certificado_generado_en')->nullable()->after('certificado_path');
        });
    }

    public function down(): void
    {
        Schema::table('estudiante', function (Blueprint $table) {
            $table->dropColumn(['certificado_path', 'certificado_generado_en']);
        });
    }
};
