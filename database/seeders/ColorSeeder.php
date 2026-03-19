<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    public function run(): void
    {
        $colores = [
            'VERDE CAÑA SUMIMASTER PE-42623',
            'MAGENTA SUMIMASTER PE-46580-FL',
            'AMARILLO MASTER SUMICOLOR 35629-02',
            'BLANCO MASTER INGECOLOR LPE 14116',
            'NEGRO MASTER INGECOLOR LPE 84213',
            'BLANCO MASTER SNETOR 53W (SOLO BOTELLA)',
            'AZUL MASTER SUMICOLOR 34852-02',
            'NARANJA MASTER COLARQUIN LPE 6493-20',
            'VERDE MASTER COLARQUIN L 3400',
            'VERDE MASTER SUMIMASTER PE-46568 -CLORO',
            'ROJO MASTER COLARQUIM LPE 14116',
            'HIFORMER ROJO FE32606510',
            'ROJO SUMIMASTER PE-6581',
            'CAFÉ MASTER SUMICOLOR PE-37735',
            'AMARILLO LIQUIDO COLORMATRIX',
            'BLANCO LIQUIDO COLORMATRIX 481-20521-1',
            'AZUL LIQUIDO COLORMATRIX 485-11379-1',
            'VERDE LIQUIDO COLORMATRIX 484-10278-1',
            'AMBAR LIQUIDO COLORMATRIX SIGMA - Q2',
            'VERDE OLIVA LIQUIDO 484-11027-1',
        ];

        foreach ($colores as $nombre) {
            Color::firstOrCreate([
                'nombre' => mb_strtoupper($nombre, 'UTF-8'),
                'activo' => true
            ]);
        }
    }
}