<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `pagos.tipo_pago` era un enum('Bancario','Oxxo','Transferencia'). El checkout
 * del estudiante guarda además 'mercadopago' (y podría guardar otros métodos),
 * valor que con STRICT_TRANS_TABLES activo lanza un error de base de datos.
 * Lo pasamos a varchar para admitir cualquier método de pago.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pagos', 'tipo_pago')) {
            return;
        }

        // Cambio directo por SQL: doctrine/dbal no maneja bien la conversión desde enum.
        DB::statement("ALTER TABLE `pagos` MODIFY `tipo_pago` VARCHAR(30) NOT NULL DEFAULT 'Bancario'");
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pagos', 'tipo_pago')) {
            return;
        }

        // Normaliza cualquier valor fuera del enum antes de revertir.
        DB::table('pagos')
            ->whereNotIn('tipo_pago', ['Bancario', 'Oxxo', 'Transferencia'])
            ->update(['tipo_pago' => 'Transferencia']);

        DB::statement("ALTER TABLE `pagos` MODIFY `tipo_pago` ENUM('Bancario','Oxxo','Transferencia') NOT NULL");
    }
};
