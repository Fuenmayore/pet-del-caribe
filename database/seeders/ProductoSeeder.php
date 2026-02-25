<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $file = base_path("database/seeders/csv/referencias.csv");
        if (!file_exists($file)) return;

        $csvData = fopen($file, "r");
        $firstline = true;

        while (($data = fgetcsv($csvData, 2000, ";")) !== FALSE) { // Usamos ";"
            // Validamos que la línea tenga las columnas necesarias
            if (empty($data) || count($data) < 9) continue;

            if (!$firstline) {
                $item = trim($data[1]); // Columna 'Item'
                if (!empty($item)) {
                    Producto::updateOrCreate(
                        ['item' => $item],
                        [
                            'descripcion'   => trim($data[2]), // 'Desc. item'
                            'area'          => trim($data[4]), // 'AREA'
                            'ciclo'         => $this->limpiarNumero($data[6]), // 'Ciclo'
                            'cavidades'     => $this->limpiarNumero($data[8]), // 'Cavidades'
                            'materia_prima' => 'PET',
                        ]
                    );
                }
            }
            $firstline = false;
        }
        fclose($csvData);
    }

    // Función auxiliar para evitar errores con números en el CSV
    private function limpiarNumero($valor) {
        $num = trim($valor);
        $num = str_replace(',', '.', $num); // Cambia comas decimales por puntos
        return is_numeric($num) ? $num : 0;
    }
}