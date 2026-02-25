<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $file = base_path("database/seeders/csv/operarios.csv");
        if (!file_exists($file)) return;

        $csvData = fopen($file, "r");
        $firstline = true;

        while (($data = fgetcsv($csvData, 2000, ";")) !== FALSE) { // Cambiado a ";" por seguridad
            // Si la línea está vacía o no tiene 2 columnas, la saltamos
            if (empty($data) || count($data) < 2) continue;

            if (!$firstline) {
                $codigo = trim($data[0]);
                $nombre = trim($data[1]);

                if (!empty($nombre)) {
                    User::updateOrCreate(
                        ['codigo_empleado' => $codigo],
                        [
                            
                            'nombre'   => $nombre,
                            'email'    => "op{$codigo}@petcaribe.com",
                            'password' => bcrypt('pet123'),
                            'activo'   => true,
                        ]
                    );
                }
            }
            $firstline = false;
        }
        fclose($csvData);
    }
}