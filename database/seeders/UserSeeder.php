<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. ADMINISTRADOR MAESTRO (Solo con tus columnas reales)
        $admin = User::updateOrCreate(
            ['email' => 'admin@petdelcaribe.com'],
            [
                'nombre'          => 'ADMINISTRADOR SISTEMA',
                'password'        => Hash::make('PetAdmin2024*'),
                'codigo_empleado' => '0000',
                'activo'          => true,
            ]
        );

        // Asignar Rol Admin
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        // 2. PROCESAR OPERARIOS DESDE EL CSV (Tu lógica original)
        $file = base_path("database/seeders/csv/operarios.csv");
        if (!file_exists($file)) return;

        $csvData = fopen($file, "r");
        $firstline = true;

        while (($data = fgetcsv($csvData, 2000, ";")) !== FALSE) {
            if (empty($data) || count($data) < 2) continue;

            if (!$firstline) {
                $codigo = trim($data[0]);
                $nombre = trim($data[1]);

                if (!empty($nombre)) {
                    // Usamos tu lógica de updateOrCreate por codigo_empleado
                    $operario = User::updateOrCreate(
                        ['codigo_empleado' => $codigo],
                        [
                            'nombre'   => $nombre,
                            'email'    => "op{$codigo}@petcaribe.com",
                            'password' => Hash::make('pet123'),
                            'activo'   => true,
                        ]
                    );

                    // Asignar Rol Operario
                    if (!$operario->hasRole('Operario')) {
                        $operario->assignRole('Operario');
                    }
                }
            }
            $firstline = false;
        }
        fclose($csvData);
    }
}