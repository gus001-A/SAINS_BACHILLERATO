<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Administrador;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        try {
            DB::beginTransaction();
            
            // Verificar si ya existe el administrador
            $existingUser = User::where('correo', 'admin@sistema.com')->first();
            
            if (!$existingUser) {
                // Crear usuario administrador
                $user = User::create([
                    'correo' => 'admin@sistema.com',
                    'contraseña' => Hash::make('12345678'),
                    'rol' => 'Administrador'
                ]);
                
                // Crear datos del administrador
                Administrador::create([
                    'usuario_id' => $user->id,
                    'nombre' => 'Administrador',
                    'apellido_paterno' => 'Principal',
                    'apellido_materno' => 'Sistema',
                    'fecha_nacimiento' => '1990-01-01',
                    'telefono' => '1234567890',
                    'sexo' => 'M'
                ]);
                
                DB::commit();
                
                $this->command->info('✅ Administrador creado exitosamente');
                $this->command->info('📧 Email: admin@sistema.com');
                $this->command->info('🔑 Contraseña: 12345678');
            } else {
                $this->command->warn('⚠️ El administrador ya existe');
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error al crear administrador: ' . $e->getMessage());
            Log::error('Error en AdminUserSeeder: ' . $e->getMessage());
        }
    }
}