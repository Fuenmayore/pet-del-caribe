<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar caché para que Spatie no se confunda
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear los Roles Maestros garantizados
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Visor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Supervisor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Operario', 'guard_name' => 'web']);

        // 3. Crear al Admin Principal
        $admin = User::updateOrCreate(
            ['email' => 'admin@petdelcaribe.com'],
            [
                'nombre'          => 'ADMINISTRADOR SISTEMA',
                'password'        => Hash::make('PetAdmin2024*'),
                'codigo_empleado' => '0000',
                'activo'          => true,
            ]
        );
        $admin->syncRoles(['Admin']);

        // 4. Procesar Archivo CSV
        $file = base_path("database/seeders/csv/usuarios.csv");
        
        if (!file_exists($file)) {
            $this->command->error("❌ El archivo CSV no se encuentra en la ruta: {$file}");
            return;
        }

        // Leemos todo el archivo línea por línea para evitar bugs de fgetcsv con finales de línea
        $lineas = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $firstline = true;
        $creados = 0;

        foreach ($lineas as $linea) {
            if ($firstline) {
                $firstline = false;
                continue;
            }

            // Explotamos por punto y coma forzosamente
            $data = explode(';', $linea);
            if (count($data) < 2) continue; // Fila corrupta

            $codigo = trim($data[0] ?? '');
            $nombre = trim($data[1] ?? '');
            $cargo  = trim($data[2] ?? ''); 
            $rolCsv = trim($data[3] ?? '');
            $correo = trim($data[4] ?? '');

            if (empty($nombre)) continue; // Si no hay nombre real, saltamos

            try {
                // ==========================================
                // LÓGICA DE DATOS RESILIENTES
                // ==========================================
                
                // Si no tiene código, fabricamos uno basado en su nombre (Ej: EMP-LEONEL)
                $prefijo = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $nombre), 0, 6));
                $codigoFinal = !empty($codigo) ? $codigo : "EMP-{$prefijo}";
                
                $emailFinal = !empty($correo) ? $correo : strtolower("op{$codigoFinal}@petdelcaribe.com");
                $passwordFinal = Hash::make(!empty($codigo) ? $codigo : 'pet123');

                // ¿Por cuál llave lo buscamos para no duplicar?
                $matchCriteria = !empty($codigo) ? ['codigo_empleado' => $codigo] : ['email' => $emailFinal];

                $usuario = User::updateOrCreate(
                    $matchCriteria,
                    [
                        'nombre'          => $nombre,
                        'email'           => $emailFinal,
                        'codigo_empleado' => $codigoFinal,
                        'password'        => $passwordFinal, 
                        'activo'          => true,
                    ]
                );

                // ==========================================
                // MAPEADOR INTELIGENTE DE ROLES
                // ==========================================
                $rolAsignar = 'Operario'; // Base
                
                $textoBusqueda = mb_strtolower($rolCsv . ' ' . $cargo, 'UTF-8');
                $textoBusqueda = strtr($textoBusqueda, ['á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u']);

                if (str_contains($textoBusqueda, 'admin')) {
                    $rolAsignar = 'Admin';
                } elseif (str_contains($textoBusqueda, 'lider') || str_contains($textoBusqueda, 'supervisor')) {
                    $rolAsignar = 'Supervisor';
                } elseif (str_contains($textoBusqueda, 'visor')) {
                    $rolAsignar = 'Visor';
                }

                // Sincronizar el rol
                $usuario->syncRoles([$rolAsignar]);
                $creados++;

            } catch (\Exception $e) {
                // Si falla (Ej: la BD rechaza el nombre), te lo mostrará en rojo en la consola.
                $this->command->error("❌ Error guardando a {$nombre}: " . $e->getMessage());
            }
        }

        $this->command->info("✅ Seeder completado. Se sincronizaron {$creados} usuarios de forma exitosa.");
    }
}