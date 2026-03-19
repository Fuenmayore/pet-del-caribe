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
        // 1. Limpiar caché de permisos (Obligatorio en Spatie)
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. DEFINICIÓN DE PERMISOS REALES DE PLANTA
        $permisos = [
            // Módulo de Turnos y Consola
            'turnos.abrir',
            'turnos.finalizar',
            'turnos.cancelar', // Peligroso: Borra el turno y sus horas
            
            // Registro de Horas
            'registro_horario.crear', // Cargar datos de la hora
            'registro_horario.editar', // Corregir una hora pasada
            
            // Control de Moldes / Referencias
            'moldes.cambiar', // Configurar el siguiente molde (Fase 2)
            'moldes.corregir_historial', // Botón "Corregir Ref." en fases pasadas
            
            // Perfiles de Operación (Recetas)
            'perfiles.crear',
            'perfiles.ver',

            // Catálogos Maestros
            'catalogos.ver',
            'catalogos.gestionar', // Crear/Editar productos, paradas, PNC, etc.

            // Reportes y OEE
            'analisis.ver',

            // Administración
            'usuarios.gestionar',
        ];

        // Crear los permisos en la BD
        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // 3. CREACIÓN DE ROLES Y ASIGNACIÓN ESTRATÉGICA

        // --- ROL: ADMIN (Tiene llave maestra) ---
        $roleAdmin = Role::firstOrCreate(['name' => 'Admin']);
        $roleAdmin->syncPermissions(Permission::all());

        // --- ROL: SUPERVISOR (Controla producción, corrige errores, pero no borra usuarios) ---
        $roleSupervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $roleSupervisor->givePermissionTo([
            'turnos.abrir', 'turnos.finalizar', 'turnos.cancelar',
            'registro_horario.crear', 'registro_horario.editar',
            'moldes.cambiar', 'moldes.corregir_historial',
            'perfiles.crear', 'perfiles.ver',
            'catalogos.ver', 'catalogos.gestionar',
            'analisis.ver'
        ]);

        // --- ROL: OPERARIO (Solo registra la realidad, no altera el pasado profundo) ---
        $roleOperario = Role::firstOrCreate(['name' => 'Operario']);
        $roleOperario->givePermissionTo([
            'turnos.abrir', 'turnos.finalizar',
            'registro_horario.crear', // Puede registrar la hora actual
            'registro_horario.editar', // Puede corregir su propia hora del turno actual
            'moldes.cambiar', // Puede cambiar de molde cuando termina una corrida
            'perfiles.crear',
        ]);

        // --- ROL: VIEW ONLY (Gerencia, Calidad externa) ---
        $roleViewOnly = Role::firstOrCreate(['name' => 'ViewOnly']);
        $roleViewOnly->givePermissionTo([
            'perfiles.ver',
            'catalogos.ver',
            'analisis.ver'
        ]);

        // 4. ASIGNAR ROL AL USUARIO INICIAL (ADMIN)
        // Asegúrate de que este correo sea el tuyo en la base de datos
        $user = User::where('email', 'admin@petdelcaribe.com')->first();
        if ($user) {
            $user->assignRole($roleAdmin);
        }
    }
}