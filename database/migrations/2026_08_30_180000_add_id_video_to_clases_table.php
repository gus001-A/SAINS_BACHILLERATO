<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clases', function (Blueprint $table) {
            if (! Schema::hasColumn('clases', 'id_video')) {
                $table->unsignedBigInteger('id_video')->nullable()->after('id_asignatura');
                $table->index('id_video');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clases', function (Blueprint $table) {
            if (Schema::hasColumn('clases', 'id_video')) {
                $table->dropColumn('id_video');
            }
        });
    }
};
