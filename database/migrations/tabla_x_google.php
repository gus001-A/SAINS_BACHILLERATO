<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('correo');
            $table->string('contraseña')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('usuario', function (Blueprint $table) {
            $table->dropColumn('google_id');
            $table->string('contraseña')->nullable(false)->change();
        });
    }
};