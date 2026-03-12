<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use App\Models\Producto;
use App\Models\Maquina;
use App\Models\ProduccionHoraria;
use App\Models\AnomaliaProduccion;
// --- NUEVOS MODELOS PARA CATÁLOGOS DINÁMICOS ---
use App\Models\PerfilOperacion;
use App\Models\MateriaPrima;
use App\Models\PncCatalogo;
use App\Models\ParadaCatalogo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ProduccionController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Produccion/Inyeccion/Index', [
            'maquinasLibres' => Maquina::where('sub_area', 'Inyeccion')
                ->whereDoesntHave('turnos', function ($query) {
                    $query->where('estado', 'Abierto');
                })->get(),

            'misTurnosActivos' => Turno::with(['maquina', 'configActiva.producto', 'configuraciones'])
                ->where('operario_id', auth()->id())
                ->where('estado', 'Abierto')
                ->get(),

            'historialTurnos' => Turno::with([
                'maquina',
                'operario',
                'configuraciones.producto',
                'configuraciones.horasProduccion'
            ])
                ->orderBy('fecha', 'desc')
                ->orderBy('numero_turno', 'desc')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function config(Maquina $maquina)
    {
        return Inertia::render('Produccion/Inyeccion/Config', ['maquina' => $maquina]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'maquina_id'   => 'required|exists:maquinas,id',
            'fecha'        => 'required|date',
            'turno'        => 'required|integer',
            'duracion_turno' => 'required|integer|in:8,12',
            'cod_operario' => 'required|string',
            'supervisor'   => 'required|string',
            'area'         => 'required|string',
        ]);

        $turno = Turno::create([
            'maquina_id'   => $request->maquina_id,
            'fecha'        => $request->fecha,
            'numero_turno' => $request->turno,
            'operario_id'  => auth()->id(),
            'supervisor'   => $request->supervisor,
            'area'         => $request->area,
            'duracion_turno' => $request->duracion_turno,
            'estado'       => 'Abierto',
        ]);

        return redirect()->route('produccion.registro', $turno->id);
    }

    /**
     * MÉTODO REGISTRO: Inyectamos los catálogos dinámicos aquí.
     */
    public function registro(Turno $turno)
    {
        $turno->load([
            'maquina',
            'configActiva.producto',
            'configuraciones' => function ($query) {
                $query->with(['producto', 'horasProduccion'])->orderBy('created_at', 'asc');
            }
        ]);

        // 1. AGREGA ESTA CONSULTA PARA SABER CUÁNTOS VAN
        $perfilesGuardados = [];
        if ($turno->configActiva) {
            $perfilesGuardados = \App\Models\PerfilOperacion::where('turno_id', $turno->id)
                ->where('config_id', $turno->configActiva->id)
                ->get();
        }

        return Inertia::render('Produccion/Inyeccion/RegistroHorario', [
            'turno' => $turno,
            // 2. ENVÍALO A LA VISTA AQUÍ:
            'perfilesGuardados' => $perfilesGuardados,

            'productos' => Producto::where('area', 'LIKE', '%INYECCIÓN%')->orderBy('descripcion')->get(['id', 'item', 'descripcion', 'ciclo', 'cavidades']),
            'materiales' => MateriaPrima::orderBy('nombre')->get(),
            'pncCatalogo' => PncCatalogo::whereIn('area', ['Inyección', 'Ambos'])->orderBy('nombre')->get(),
            'paradasCatalogo' => ParadaCatalogo::orderBy('codigo')->get(),
            'anomalias' => AnomaliaProduccion::all(),
        ]);
    }

    public function guardarConfiguracion(Request $request, Turno $turno)
    {
        $validated = $request->validate([
            // Agregamos el ID de configuración para saber si estamos editando o creando
            'config_id'   => 'nullable|exists:produccion_config,id',
            'producto_id' => 'required|exists:productos,id',
            'color'       => 'nullable|string|max:50',
            'mezcla'      => 'required|array',
            'mezcla.*.materia_prima_id' => 'required|exists:materia_prima,id'
        ]);

        /**
         * Si mandamos 'config_id', el sistema busca esa fase y la actualiza.
         * Si no mandamos 'config_id', crea una fase nueva (Cambio de Molde).
         */
        \App\Models\ProduccionConfig::updateOrCreate(
            ['id' => $request->config_id], // Criterio de búsqueda
            [
                'turno_id'    => $turno->id,
                'producto_id' => $request->producto_id,
                'mezcla_materiales' => [
                    'color'      => strtoupper($request->color),
                    'materiales' => $request->mezcla
                ],
            ]
        );

        // Limpiamos relaciones para que Inertia mande los datos frescos
        $turno->unsetRelation('configActiva');
        $turno->unsetRelation('configuraciones');
        $turno->load(['configActiva.producto', 'configuraciones.producto', 'configuraciones.horasProduccion']);

        return back()->with('success', $request->config_id ? 'Referencia corregida' : 'Nueva Referencia Iniciada');
    }

    public function guardarHora(Request $request)
    {
        $validated = $request->validate([
            'id'              => 'nullable|uuid',
            'config_id'       => 'required|exists:produccion_config,id',
            'hora'            => 'required|string',
            'unidades_buenas' => 'required|integer|min:0',
            'num_vale'        => 'nullable|string',
            'pnc'             => 'nullable|array'
        ]);

        // Sincronización blindada
        ProduccionHoraria::updateOrCreate(
            [
                // Buscamos por el UUID si existe
                'id' => $validated['id'] ?? null,
            ],
            [
                // Todos estos campos se insertarán si el ID no existe 
                // o se actualizarán si el ID ya existe.
                'config_id'          => $validated['config_id'],
                'hora'               => $validated['hora'], // <--- ESTO ES LO QUE FALTABA
                'unidades_buenas'    => $validated['unidades_buenas'],
                'num_vale_inyectora' => $validated['num_vale'],
                'pnc_detalle'        => $validated['pnc'] ?? []
            ]
        );

        return back()->with('message', 'Registro de las ' . $validated['hora'] . ' sincronizado correctamente.');
    }
    public function finalizar(Turno $turno)
    {
        $turno->update(['estado' => 'Cerrado']);
        return redirect()->route('produccion.index')->with('message', 'Turno finalizado con éxito');
    }

    public function analisis(Turno $turno)
    {
        $turno->load([
            'maquina',
            'operario',
            'configuraciones.producto',
            'configuraciones.horasProduccion',
            // Agregamos .registrador al final de esta línea 👇
            'configuraciones.perfilesOperacion.registrador' // <--- ¡Este último punto es vital!
        ]);

        return inertia('Produccion/Inyeccion/Analisis', [
            'turno' => $turno,
            // Mandamos los catálogos para mapear nombres reales en el reporte
            'pncCatalogo' => \App\Models\PncCatalogo::all(),
            'paradasCatalogo' => \App\Models\ParadaCatalogo::all(),
            'materiales' => \App\Models\MateriaPrima::all()
        ]);
    }

    public function destroy(Turno $turno)
    {
        DB::transaction(function () use ($turno) {
            foreach ($turno->configuraciones as $config) {
                $config->horasProduccion()->delete();
            }
            $turno->configuraciones()->delete();
            $turno->delete();
        });

        return redirect()->route('produccion.index')->with('message', 'Turno cancelado con éxito');
    }

    // Agrega este método justo arriba de tu guardarPerfil
    public function crearPerfil(Turno $turno)
    {
        $turno->load(['maquina', 'configActiva.producto']);

        // Buscamos si ya existen lecturas de perfil para esta configuración en este turno
        $perfilesGuardados = [];
        if ($turno->configActiva) {
            $perfilesGuardados = \App\Models\PerfilOperacion::with('registrador:id,nombre') // Traemos el nombre de quien lo llenó
                ->where('turno_id', $turno->id)
                ->where('config_id', $turno->configActiva->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return Inertia::render('Produccion/Inyeccion/PerfilOperacion', [
            'turno' => $turno,
            'perfilesGuardados' => $perfilesGuardados
        ]);
    }


    // 2. Agrega este método al final de tu controlador (antes de la última llave '}')
    public function guardarPerfil(Request $request, Turno $turno)
    {
        $validated = $request->validate([
            'config_id'           => 'required|exists:produccion_config,id',
            'hora_medicion'       => 'required|string',
            'clamp_expulsor'      => 'nullable|array',
            'inyeccion_carga'     => 'nullable|array',
            'temperaturas'        => 'nullable|array',
            'perifericos_presion' => 'nullable|array',
            'observaciones'       => 'nullable|string'
        ]);

        PerfilOperacion::create([
            'turno_id'            => $turno->id,
            'config_id'           => $validated['config_id'],
            'hora_medicion'       => $validated['hora_medicion'],
            'clamp_expulsor'      => $validated['clamp_expulsor'] ?? [],
            'inyeccion_carga'     => $validated['inyeccion_carga'] ?? [],
            'temperaturas'        => $validated['temperaturas'] ?? [],
            'perifericos_presion' => $validated['perifericos_presion'] ?? [],
            'observaciones'       => $validated['observaciones'],
            'registrado_por'      => auth()->id(), // El sistema sabe quién llenó la receta
        ]);

        return back()->with('message', 'Perfil de operación registrado en la bitácora.');
    }
}
