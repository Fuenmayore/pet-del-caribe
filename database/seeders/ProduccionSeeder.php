<?php

namespace Database\Seeders;

use App\Models\Maquina;
use App\Models\AnomaliaProduccion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProduccionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Cargar Máquinas Reales (según BASE REFERENCIAS.csv)
        $maquinas = [
            'AOLI 1', 'AOLI 2', 'AOLI 3 II', 'AOLI 4', 
            'WELTEC 1', 'WELTEC 2', 'WELTEC 3'
        ];

        foreach ($maquinas as $nombre) {
            Maquina::updateOrCreate(
                ['nombre' => $nombre],
                ['sub_area' => 'Inyeccion']
            );
        }

        // 2. Cargar Motivos de Paro Reales (según MOTIVOS PARO.csv)
        // He seleccionado una muestra representativa de tu archivo
        $motivos = [
            ['codigo' => '3', 'descripcion' => 'Fluido electrico externo (ADMIN)'],
            ['codigo' => '4', 'descripcion' => 'Falla aire (PERIFERICOS)'],
            ['codigo' => '13', 'descripcion' => 'Cambio molde / portapref (GENERAL)'],
            ['codigo' => '24', 'descripcion' => 'Sistema de carga (MECANICAS)'],
            ['codigo' => '30', 'descripcion' => 'Tobera (MECANICAS)'],
            ['codigo' => '34', 'descripcion' => 'PLC (ELECTRONICAS)'],
            ['codigo' => '40', 'descripcion' => 'Manguera (HIDRAULICAS)'],
            ['codigo' => '62', 'descripcion' => 'PNC (GENERAL)'],
        ];

        foreach ($motivos as $motivo) {
            AnomaliaProduccion::updateOrCreate(
                ['codigo' => $motivo['codigo']],
                ['descripcion' => $motivo['descripcion']]
            );
        }
    }
}