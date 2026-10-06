<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->boolean('usado')->default(false);
            $table->foreignId('usuario_uso')->nullable()->constrained('usuario')->nullOnDelete();
            $table->foreignId('usuario_genero')->constrained('usuario')->onDelete('cascade');
            $table->string('estatus')->default('activo');
            $table->date('fecha_genero');
            $table->date('fecha_uso')->nullable();
            $table->string('tipo_descuento');
            $table->decimal('valor_descuento', 10, 2)->nullable();
            $table->timestamps(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};