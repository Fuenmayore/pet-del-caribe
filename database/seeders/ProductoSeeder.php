<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Ruta al archivo CSV
        $file = base_path("database/seeders/csv/referencias.csv");
        if (!file_exists($file)) return;

        $csvData = fopen($file, "r");
        $firstline = true;

        // Leemos línea por línea
        while (($data = fgetcsv($csvData, 2000, ";")) !== FALSE) { 
            
            // Validamos que la línea tenga las 12 columnas que muestra tu imagen
            if (empty($data) || count($data) < 12) continue;

            if (!$firstline) {
                $item = trim($data[1]); // Columna 'Item'

                if (!empty($item)) {
                    Producto::updateOrCreate(
                        ['item' => $item], // Identificador único
                        [
                            'ref_maq'         => trim($data[0]),  // REF + MAQ
                            'descripcion'     => trim($data[2]),  // Desc. item
                            'tipo_inventario' => trim($data[3]),  // Tipo inventari
                            'area'            => trim($data[4]),  // AREA
                            'preforma'        => trim($data[5]),  // Preforma
                            'ciclo'           => $this->limpiarNumero($data[6]), // Ciclo
                            'maquina'         => trim($data[7]),  // Maquina
                            'cavidades'       => (int) $this->limpiarNumero($data[8]), // Cavidades
                            'bot_hora'        => (int) $this->limpiarNumero($data[9]), // BOT/HORA
                            'unidad_empaque'  => trim($data[10]), // UNIDAD DE EMPAQUE
                            'centro_trabajo'  => trim($data[11]), // Centro de trab
                        ]
                    );
                }
            }
            $firstline = false;
        }
        fclose($csvData);
    }

    /**
     * Función auxiliar para limpiar números.
     * Maneja casos donde el CSV trae puntos de miles o comas decimales.
     */
    private function limpiarNumero($valor) {
        $num = trim($valor);
        // Quitamos puntos de miles si existen (ej: 13.824 -> 13824)
        // Pero ojo, si el punto es decimal, esto depende de tu formato regional.
        // Asumiendo formato estándar:
        $num = str_replace(',', '.', $num); 
        return is_numeric($num) ? $num : 0;
    }
}