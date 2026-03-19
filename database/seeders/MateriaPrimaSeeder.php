<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MateriaPrima;

class MateriaPrimaSeeder extends Seeder
{
    public function run(): void
    {
        $materiales = [
            ['nombre' => 'PET CONVENCIONAL CLEARTUF', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'PET CONVENCIONAL JADE IMP', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'PET CONVENCIONAL RAMAPET', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'PET CONVENCIONAL RELPET QH 5821', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'PET PAPET COOL LOTTE CHEMICAL', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'PET OCTAL RH03 IMP', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'PET CHINA RESOURCES', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIESTER CHIP BOTTLE YS-Y01', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'PET BAJA CRISTANILIZACION', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'PET EKO', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'RESINA PET-PCR RECYCLAPET', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIETILENO HD MI 02', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIETILENO HDPE BRASKETM JV060U', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIET INYECCION HD MI 0,35', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIET INYECCION HD HDPE ME 2500S2N', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIET SOPLADO HD MARLEX HHM 5502BN', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIET INYECCION HD MI 20', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIETILENO HD MI 07', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLYPRO/ENO SABIC QR6701K', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIET INYECCION SNETOR HD8060', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIET INYECCION DLH5207', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIET INYECC LD MI 02', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLIETILENO ICELENE SOPLADO HB3553', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
            ['nombre' => 'POLYET INYEC HD FORMOLENE AVEC UV LH6008', 'tipo' => 'Resina', 'unidad_medida' => 'KG'],
        ];

        foreach ($materiales as $m) {
            MateriaPrima::updateOrCreate(
                ['nombre' => $m['nombre']], // Evita duplicidad de nombres
                [
                    'tipo' => $m['tipo'],
                    'unidad_medida' => $m['unidad_medida']
                ]
            );
        }
    }
}