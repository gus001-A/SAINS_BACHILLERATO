<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo_pago', ['Bancario', 'Oxxo', 'Transferencia']);
            $table->foreignId('alumno_pago')->constrained('estudiante')->onDelete('cascade');
            $table->datetime('fecha_pago');
            $table->decimal('monto_pago', 10, 2);
            $table->string('estatus')->default('pendiente');
            $table->string('referencia_pago', 100)->unique();
            $table->string('comprobante')->nullable();
            $table->foreignId('usuario_revision')->nullable()->constrained('usuario')->nullOnDelete();
            $table->datetime('fecha_aprueba')->nullable();
            $table->text('nota_usuario')->nullable();
            $table->timestamps(false);
            
            $table->index('estatus');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};