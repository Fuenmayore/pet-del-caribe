<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PncCatalogo;

class PncCatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conceptos = [
            // ÁREA: SOPLADO
            ['nombre' => 'BOTELLA APTA PARA MOLER', 'area' => 'Soplado'],
            ['nombre' => 'BIDON SUBPRODUCTO', 'area' => 'Soplado'],
            ['nombre' => 'NO CONFORMIDAD PREFORMA', 'area' => 'Soplado'],
            ['nombre' => 'BOTELLA CONTAMINADA', 'area' => 'Soplado'],
            ['nombre' => 'PRUEBAS CALIDAD', 'area' => 'Soplado'],

            // ÁREA: INYECCIÓN
            ['nombre' => 'PREFORMA PARA MOLER', 'area' => 'Inyección'],
            ['nombre' => 'PREFORMA CONTAMINADA', 'area' => 'Inyección'],
            ['nombre' => 'TORTA', 'area' => 'Inyección'],
            ['nombre' => 'TAPA/ASA CONTAMINADAS', 'area' => 'Inyección'],
            ['nombre' => 'TORTA COLOR', 'area' => 'Inyección'],
            ['nombre' => 'POLVILLO TONGDA', 'area' => 'Inyección'],

            // AMBAS ÁREAS
            ['nombre' => 'BARRIDO', 'area' => 'Ambos'],
        ];

        foreach ($conceptos as $concepto) {
            PncCatalogo::updateOrCreate(
                ['nombre' => $concepto['nombre']], // Evita duplicados si lo corres dos veces
                ['area' => $concepto['area']]
            );
        }
    }
}