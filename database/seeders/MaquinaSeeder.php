<?php

namespace Database\Seeders;

use App\Models\Maquina;
use Illuminate\Database\Seeder;

class MaquinaSeeder extends Seeder
{
    public function run(): void
    {
        $maquinas = [
            // --- INYECCIÓN ---
            ['nombre' => 'WELLTEC 1', 'abreviacion' => 'WT1', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'WELLTEC 2', 'abreviacion' => 'WT2', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'WELLTEC 3', 'abreviacion' => 'WT3', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'WELLTEC 4', 'abreviacion' => 'WT4', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'HAITIAN 1', 'abreviacion' => 'HT1', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'HAITIAN 2', 'abreviacion' => 'HT2', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'HAITIAN 3', 'abreviacion' => 'HT3', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'HAITIAN 4', 'abreviacion' => 'HT4', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'TATMING 2100-3', 'abreviacion' => 'TM 2100-3', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'DAELONG TAPA', 'abreviacion' => 'DL TAPA', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'WELLTEC TP', 'abreviacion' => 'WT TP', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'HAITAN TAPA 1', 'abreviacion' => 'HT TP', 'sub_area' => 'Inyeccion'],
            ['nombre' => 'HAITIAN TAPA 2', 'abreviacion' => 'HT TP2', 'sub_area' => 'Inyeccion'],

            // --- SOPLADO ---
            ['nombre' => 'QUINKO LEOPAR', 'abreviacion' => 'QKL2', 'sub_area' => 'Soplado'],
            ['nombre' => 'QUINKO LEOPAR', 'abreviacion' => 'QKL3', 'sub_area' => 'Soplado'],
            ['nombre' => 'QUINKO LEOPAR', 'abreviacion' => 'QKL4', 'sub_area' => 'Soplado'],
            ['nombre' => 'QUINKO LEOPAR', 'abreviacion' => 'QKL5', 'sub_area' => 'Soplado'],
            ['nombre' => 'QUINKO 1', 'abreviacion' => 'QK1', 'sub_area' => 'Soplado'],
            ['nombre' => 'QUINKO 2', 'abreviacion' => 'QK2', 'sub_area' => 'Soplado'],
            ['nombre' => 'QUINKO 4', 'abreviacion' => 'QK4', 'sub_area' => 'Soplado'],
            ['nombre' => 'AOLI 1', 'abreviacion' => 'AL1', 'sub_area' => 'Soplado'],
            ['nombre' => 'AOLI 4', 'abreviacion' => 'AL4', 'sub_area' => 'Soplado'],
            ['nombre' => 'AOLI 5', 'abreviacion' => 'AL5', 'sub_area' => 'Soplado'],
            ['nombre' => 'AOK 1', 'abreviacion' => null, 'sub_area' => 'Soplado'], // En blanco en la imagen
            ['nombre' => 'AOK 2', 'abreviacion' => null, 'sub_area' => 'Soplado'], // En blanco en la imagen
            ['nombre' => 'MEGA 1', 'abreviacion' => 'MG1', 'sub_area' => 'Soplado'],
            ['nombre' => 'MEGA 2', 'abreviacion' => 'MG2', 'sub_area' => 'Soplado'],
            ['nombre' => 'SEMI 32', 'abreviacion' => 'S32', 'sub_area' => 'Soplado'],
            ['nombre' => 'SEMI 35', 'abreviacion' => 'S35', 'sub_area' => 'Soplado'],
            ['nombre' => 'SEMI 42', 'abreviacion' => 'S42', 'sub_area' => 'Soplado'],
            ['nombre' => 'SEMI 45', 'abreviacion' => 'S45', 'sub_area' => 'Soplado'],
        ];

        foreach ($maquinas as $maq) {
            // Usamos updateOrCreate por si el seeder se corre múltiples veces, 
            // no te duplique la información en la base de datos.
            Maquina::updateOrCreate(
                [
                    'nombre' => $maq['nombre'], 
                    'abreviacion' => $maq['abreviacion'] // Diferenciador clave para las QUINKO repetidas
                ], 
                [
                    'sub_area' => $maq['sub_area']
                ]
            );
        }
        
        $this->command->info('✅ Máquinas de Inyección y Soplado sincronizadas correctamente.');
    }
}