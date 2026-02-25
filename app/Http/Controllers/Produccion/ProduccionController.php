<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use App\Models\Producto;
use App\Models\Maquina;
use App\Models\ProduccionHoraria;
use App\Models\RegistrosPnc;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;


class ProduccionController extends Controller
{
    /**
     * Dashboard Principal: Muestra máquinas libres y turnos activos del operario.
     */
    public function index()
{
    return Inertia::render('Produccion/Inyeccion/Index', [
        'maquinasLibres' => Maquina::where('sub_area', 'Inyeccion')
            ->whereDoesntHave('turnos', function ($query) {
                $query->where('estado', 'Abierto');
            })->get(),

        'misTurnosActivos' => Turno::with(['maquina', 'produccionConfig.producto']) // <--- Cargamos el producto
            ->where('operario_id', auth()->id())
            ->where('estado', 'Abierto')
            ->get()
    ]);
}



    /**
     * Formulario de configuración para una máquina específica.
     */
    public function config(Maquina $maquina)
{
    return Inertia::render('Produccion/Inyeccion/Config', [
        'maquina' => $maquina,
        // Filtramos solo los productos del área de Inyección
        'productos' => Producto::where('area', 'LIKE', '%INYECCIÓN%')
                                ->orderBy('descripcion')
                                ->get(['id', 'item', 'descripcion', 'ciclo', 'cavidades']),
        // Lista base para el buscador de materias primas
        'materiasBase' => [
            ['id' => 1, 'nombre' => 'PET VIRGEN'],
            ['id' => 2, 'nombre' => 'PET MOLIDO'],
            ['id' => 3, 'nombre' => 'MASTERBATCH AZUL'],
            ['id' => 4, 'nombre' => 'MASTERBATCH VERDE'],
        ]
    ]);
}

    /**
     * Inicia un nuevo turno (Crea Turno + Configuración F1).
     */
public function store(Request $request)
{
    // 1. Validamos (Asegúrate que estos nombres coincidan con tu useForm en Vue)
    $request->validate([
        'maquina_id'   => 'required|exists:maquinas,id', 
        'fecha'        => 'required|date',
        'turno'        => 'required|integer', // Este es el dato que viene de Vue
        'cod_operario' => 'required|string',
        'supervisor'   => 'required|string',
    ]);

    return DB::transaction(function () use ($request) {
        // 2. Creamos el Turno
        // AQUÍ ESTABA EL ERROR: La columna se llama 'numero_turno'
        $turno = Turno::create([
            'maquina_id'   => $request->maquina_id,
            'fecha'        => $request->fecha,
            'numero_turno' => $request->turno, // MAPEO: columna 'numero_turno' <--- dato 'turno'
            'operario_id'  => auth()->id(),
            'supervisor'   => $request->supervisor,
            'estado'       => 'Abierto',
        ]);



        // 3. Redirección
        return redirect()->route('produccion.registro', $turno->id);
    });
}

    /**
     * Vista del Reporte Horario (F1) para una máquina específica.
     */
    public function registro(Turno $turno)
    {
        return Inertia::render('Produccion/Inyeccion/RegistroHorario', [
            'turno' => $turno->load('maquina', 'produccionConfig.producto') // <--- También aquí
        ]);
    }

    // app/Http/Controllers/Produccion/ProduccionController.php

public function guardarHora(Request $request)
{
    // 1. Validamos los datos que vienen del F1 (Vue)
    $validated = $request->validate([
        'config_id' => 'required|uuid|exists:produccion_config,id',
        'hora' => 'required',
        'unidades_buenas' => 'required|integer|min:0',
        'pnc' => 'array' // Lista de anomalías encontradas en esa hora
    ]);

    // 2. Usamos una transacción para asegurar que se guarde todo o nada
    return DB::transaction(function () use ($validated) {
        
        // Creamos el registro horario (F1)
        $hora = ProduccionHoraria::updateOrCreate(
            [
                'config_id' => $validated['config_id'],
                'hora' => $validated['hora']
            ],
            ['unidades_buenas' => $validated['unidades_buenas']]
        );

        // Si hay desperdicio (PNC), lo registramos vinculándolo a la hora
        if (!empty($validated['pnc'])) {
            foreach ($validated['pnc'] as $falla) {
                RegistrosPnc::updateOrCreate(
                    ['produccion_hora_id' => $hora->id, 'anomalia_id' => $falla['id']],
                    ['unid_malas' => $falla['cantidad']]
                );
            }
        }

        return response()->json(['status' => 'Sincronizado', 'id' => $hora->id]);
    });
}
}