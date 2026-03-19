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

            // ÁREA: INYECCIÓN (Lista de Inspección)
            ['nombre' => 'QUEMADA', 'area' => 'Inyección'],
            ['nombre' => 'RAYAS', 'area' => 'Inyección'],
            ['nombre' => 'BURBUJAS EN EL FONDO', 'area' => 'Inyección'],
            ['nombre' => 'BURBUJAS EN EL CUERPO', 'area' => 'Inyección'],
            ['nombre' => 'AGUA', 'area' => 'Inyección'],
            ['nombre' => 'PUNTO NEGRO', 'area' => 'Inyección'],
            ['nombre' => 'OPACIDAD', 'area' => 'Inyección'],
            ['nombre' => 'FONDO BLANCO', 'area' => 'Inyección'],
            ['nombre' => 'FRACTURA', 'area' => 'Inyección'],
            ['nombre' => 'ESPIGA HUECA', 'area' => 'Inyección'],
            ['nombre' => 'INCOMPLETA', 'area' => 'Inyección'],
            ['nombre' => 'REBABA EN TERMINADO', 'area' => 'Inyección'],
            ['nombre' => 'REBABA EN EL CIERRE', 'area' => 'Inyección'],
            ['nombre' => 'REBABA EN EL ANILLO DE SOPORTE', 'area' => 'Inyección'],
            ['nombre' => 'CUELLO JIRAFA', 'area' => 'Inyección'],
            // Nota: El ítem 16 es "Agua", pero ya lo agregamos en el ítem 5. Evitamos duplicar.
            ['nombre' => 'ONDULACIONES', 'area' => 'Inyección'],
            ['nombre' => 'ARRASTRE', 'area' => 'Inyección'],
            ['nombre' => 'PICO FALLO', 'area' => 'Inyección'],
            ['nombre' => 'ESPIGA LARGA', 'area' => 'Inyección'],
            ['nombre' => 'ROSCA FALLA', 'area' => 'Inyección'],
            ['nombre' => 'DESLAMINACIÓN', 'area' => 'Inyección'],
            ['nombre' => 'BETAS', 'area' => 'Inyección'],
            ['nombre' => 'GOTA DE MATERIAL', 'area' => 'Inyección'],
            ['nombre' => 'MALTRATO EN LA ROSCA DE LA TAPA', 'area' => 'Inyección'],
            ['nombre' => 'MALTRATO EN LINNER DE LA TAPA', 'area' => 'Inyección'],
            ['nombre' => 'MALTRATO EN EL PRESCITO DE LA TAPA', 'area' => 'Inyección'],
            ['nombre' => 'MALTRATO EN EL CUERPO DE LA TAPA', 'area' => 'Inyección'],
            ['nombre' => 'CONTAMINACIÓN', 'area' => 'Inyección'],
            ['nombre' => 'FILTRACIÓN', 'area' => 'Inyección'],

            // Materiales de Scrap base
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
                ['nombre' => mb_strtoupper($concepto['nombre'], 'UTF-8')], // Evita duplicados si lo corres dos veces y asegura mayúsculas
                ['area' => $concepto['area']]
            );
        }
    }
}