<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParadaCatalogo;

class ParadaCatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $fallas = [
            // ADMIN / GENERAL
            ['codigo' => '3', 'falla' => 'Fluido electrico externo', 'categoria' => 'ADMIN'],
            ['codigo' => '10', 'falla' => 'Habilitación cavidades', 'categoria' => 'GENERAL'],
            ['codigo' => '11', 'falla' => 'Cavidades anuladas', 'categoria' => 'GENERAL'],
            ['codigo' => '12', 'falla' => 'Ciclo alto', 'categoria' => 'GENERAL'],
            ['codigo' => '13', 'falla' => 'Cambio molde / portapref', 'categoria' => 'GENERAL'],
            ['codigo' => '14', 'falla' => 'Cambio color', 'categoria' => 'GENERAL'],
            ['codigo' => '15', 'falla' => 'Calentamiento y arranque', 'categoria' => 'GENERAL'],
            ['codigo' => '16', 'falla' => 'Manejo', 'categoria' => 'GENERAL'],
            ['codigo' => '17', 'falla' => 'Falta de operario', 'categoria' => 'ADMIN'],
            ['codigo' => '18', 'falla' => 'Falta de espacio', 'categoria' => 'ADMIN'],
            ['codigo' => '19', 'falla' => 'Falta de preforma / MP', 'categoria' => 'ADMIN'],
            ['codigo' => '20', 'falla' => 'Arreglo puesto', 'categoria' => 'ADMIN'],
            ['codigo' => '21', 'falla' => 'One Caribe', 'categoria' => 'ADMIN'],
            ['codigo' => '22', 'falla' => 'Pruebas calidad', 'categoria' => 'ADMIN'],
            ['codigo' => '23', 'falla' => 'Falta cartoneria/bolsa/colorante', 'categoria' => 'ADMIN'],
            ['codigo' => '56', 'falla' => 'Contaminacion MP', 'categoria' => 'ADMIN'],
            ['codigo' => '57', 'falla' => 'Otras Admin', 'categoria' => 'ADMIN'],
            ['codigo' => '59', 'falla' => 'Tiempo no reportado', 'categoria' => 'GENERAL'],
            ['codigo' => '62', 'falla' => 'PNC', 'categoria' => 'GENERAL'],
            ['codigo' => '72', 'falla' => 'F-operario TONGDA', 'categoria' => 'ADMIN'],
            ['codigo' => '73', 'falla' => 'Falta de operario Inyección', 'categoria' => 'ADMIN'],
            ['codigo' => '74', 'falla' => 'Cambio de preforma / MP', 'categoria' => 'ADMIN'],
            ['codigo' => 'P1', 'falla' => 'Cumplimiento de pedido', 'categoria' => 'ADMIN'],

            // PERIFERICOS
            ['codigo' => '4', 'falla' => 'Falla aire', 'categoria' => 'PERIFERICOS'],
            ['codigo' => '5', 'falla' => 'Sistema enfriamiento', 'categoria' => 'PERIFERICOS'],
            ['codigo' => '76', 'falla' => 'Fluido electrico interno', 'categoria' => 'PERIFERICOS'],
            ['codigo' => '77', 'falla' => 'Fallo subestación eléctrica', 'categoria' => 'PERIFERICOS'],

            // MECANICAS
            ['codigo' => '24', 'falla' => 'Sistema de carga', 'categoria' => 'MECANICAS'],
            ['codigo' => '25', 'falla' => 'Sistema de descarga', 'categoria' => 'MECANICAS'],
            ['codigo' => '26', 'falla' => 'Transfer', 'categoria' => 'MECANICAS'],
            ['codigo' => '27', 'falla' => 'Portapreforma', 'categoria' => 'MECANICAS'],
            ['codigo' => '28', 'falla' => 'Defecto de calidad', 'categoria' => 'MECANICAS'],
            ['codigo' => '29', 'falla' => 'Expulsor', 'categoria' => 'MECANICAS'],
            ['codigo' => '30', 'falla' => 'Tobera', 'categoria' => 'MECANICAS'],
            ['codigo' => '31', 'falla' => 'Motor', 'categoria' => 'MECANICAS'],
            ['codigo' => '32', 'falla' => 'Bebedero', 'categoria' => 'MECANICAS'],
            ['codigo' => '33', 'falla' => 'Banda', 'categoria' => 'MECANICAS'],
            ['codigo' => '58', 'falla' => 'Otras Mecanicas', 'categoria' => 'MECANICAS'],
            ['codigo' => '60', 'falla' => 'Empacadora', 'categoria' => 'MECANICAS'],
            ['codigo' => '61', 'falla' => 'Horno', 'categoria' => 'MECANICAS'],
            ['codigo' => '64', 'falla' => 'Limpieza portapreformas', 'categoria' => 'MECANICAS'],
            ['codigo' => '65', 'falla' => 'Limpieza varillas de estirado', 'categoria' => 'MECANICAS'],
            ['codigo' => '66', 'falla' => 'Estrella de carga', 'categoria' => 'MECANICAS'],
            ['codigo' => '68', 'falla' => 'Sistema de estirado', 'categoria' => 'MECANICAS'],
            ['codigo' => '69', 'falla' => 'Riel salida de botellas', 'categoria' => 'MECANICAS'],

            // ELECTRONICAS / ELECTRICAS
            ['codigo' => '34', 'falla' => 'PLC', 'categoria' => 'ELECTRONICAS'],
            ['codigo' => '35', 'falla' => 'Corto circuito', 'categoria' => 'ELECTRONICAS'],
            ['codigo' => '36', 'falla' => 'Sensores', 'categoria' => 'ELECTRONICAS'],
            ['codigo' => '37', 'falla' => 'Drive', 'categoria' => 'ELECTRONICAS'],
            ['codigo' => '38', 'falla' => 'Tolva', 'categoria' => 'ELECTRONICAS'],
            ['codigo' => '39', 'falla' => 'Fusible', 'categoria' => 'ELECTRONICAS'],
            ['codigo' => '47', 'falla' => 'Breaker', 'categoria' => 'ELECTRICAS'],
            ['codigo' => '48', 'falla' => 'Cable', 'categoria' => 'ELECTRICAS'],
            ['codigo' => '49', 'falla' => 'Resistencias', 'categoria' => 'ELECTRICAS'],
            ['codigo' => '50', 'falla' => 'Fusible', 'categoria' => 'ELECTRICAS'],
            ['codigo' => '63', 'falla' => 'Colada', 'categoria' => 'ELECTRICAS'],
            ['codigo' => '71', 'falla' => 'Pulsadores', 'categoria' => 'ELECTRONICAS'],

            // HIDRAULICAS / NEUMATICAS
            ['codigo' => '40', 'falla' => 'Manguera', 'categoria' => 'HIDRAULICAS'],
            ['codigo' => '41', 'falla' => 'Racores', 'categoria' => 'HIDRAULICAS'],
            ['codigo' => '42', 'falla' => 'Falla hidraulica', 'categoria' => 'HIDRAULICAS'],
            ['codigo' => '43', 'falla' => 'Fuga agua / aceite', 'categoria' => 'HIDRAULICAS'],
            ['codigo' => '44', 'falla' => 'Falla neumatica', 'categoria' => 'NEUMATICAS'],
            ['codigo' => '45', 'falla' => 'Valvula soplado / presoplado', 'categoria' => 'NEUMATICAS'],
            ['codigo' => '46', 'falla' => 'Cilindros', 'categoria' => 'NEUMATICAS'],

            // MOLDE
            ['codigo' => '51', 'falla' => 'Tornilleria molde', 'categoria' => 'MOLDE'],
            ['codigo' => '52', 'falla' => 'Machos / Hembras', 'categoria' => 'MOLDE'],
            ['codigo' => '53', 'falla' => 'Casquillo', 'categoria' => 'MOLDE'],
            ['codigo' => '54', 'falla' => 'Fuga molde', 'categoria' => 'MOLDE'],
            ['codigo' => '55', 'falla' => 'Pulimiento molde', 'categoria' => 'MOLDE'],
            ['codigo' => '67', 'falla' => 'Unidad de cierre del molde', 'categoria' => 'MOLDE'],
            ['codigo' => '70', 'falla' => 'Guias molde', 'categoria' => 'MOLDE'],
            ['codigo' => '75', 'falla' => 'Cambio de placa', 'categoria' => 'MOLDE'],

            // FALLAS SIN CATEGORÍA EXPLÍCITA (80 - 99)
            ['codigo' => '80', 'falla' => 'Error en programación', 'categoria' => 'SISTEMAS'],
            ['codigo' => '81', 'falla' => 'Relevo de máquina', 'categoria' => 'ADMIN'],
            ['codigo' => '86', 'falla' => 'Falla caja reductora', 'categoria' => 'MECANICAS'],
            ['codigo' => '87', 'falla' => 'Falla sistema de lubricación', 'categoria' => 'MECANICAS'],
            ['codigo' => '88', 'falla' => 'Falla tornillo de inyección (Husillo)', 'categoria' => 'MECANICAS'],
            ['codigo' => '89', 'falla' => 'Falla aspiradora de material', 'categoria' => 'PERIFERICOS'],
            ['codigo' => '94', 'falla' => 'Falla de pantalla (HMI)', 'categoria' => 'ELECTRONICAS'],
            ['codigo' => '95', 'falla' => 'Falla control de temperatura barril', 'categoria' => 'ELECTRONICAS'],
        ];

        foreach ($fallas as $falla) {
            ParadaCatalogo::updateOrCreate(
                ['codigo' => $falla['codigo']],
                [
                    'falla' => $falla['falla'],
                    'categoria' => $falla['categoria']
                ]
            );
        }
    }
}