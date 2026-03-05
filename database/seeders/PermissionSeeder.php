<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. DEFINICIÓN DE MÓDULOS Y PERMISOS
        $modulos = [
            'usuarios'   => ['ver', 'crear', 'editar', 'eliminar'],
            'roles'      => ['ver', 'crear', 'editar', 'eliminar'],
            'produccion' => ['ver', 'crear', 'editar', 'eliminar', 'finalizar', 'reportes'],
            'inventario' => ['ver', 'ajustar', 'movimientos'], // Futuro módulo
            'configuracion' => ['ver', 'actualizar'],
        ];

        $allPermissions = [];

        foreach ($modulos as $modulo => $acciones) {
            foreach ($acciones as $accion) {
                $permissionName = "{$modulo}.{$accion}";
                Permission::firstOrCreate(['name' => $permissionName]);
                $allPermissions[] = $permissionName;
            }
        }

        // 2. CREACIÓN DE ROLES Y ASIGNACIÓN
        
        // Super Admin: Tiene TODO
        $roleAdmin = Role::firstOrCreate(['name' => 'ADMINISTRADOR']);
        $roleAdmin->syncPermissions($allPermissions);

        // Producción: Solo lo relacionado a su área
        $roleProduccion = Role::firstOrCreate(['name' => 'PRODUCCION']);
        $roleProduccion->syncPermissions(
            Permission::where('name', 'like', 'produccion.%')->get()
        );

        // Consulta/Visualizador: Solo lectura
        $roleViewer = Role::firstOrCreate(['name' => 'VISUALIZADOR']);
        $roleViewer->syncPermissions(
            Permission::where('name', 'like', '%.ver')->get()
        );

        // 3. ASIGNAR ROL AL USUARIO INICIAL (TÚ)
        // Ajusta el email a tu usuario real
        $user = User::where('email', 'admin@tuempresa.com')->first();
        if ($user) {
            $user->assignRole($roleAdmin);
        }
    }
}