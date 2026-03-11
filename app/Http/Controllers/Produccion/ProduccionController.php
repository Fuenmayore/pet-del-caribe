<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use App\Models\Producto;
use App\Models\Maquina;
use App\Models\ProduccionHoraria;
use App\Models\AnomaliaProduccion;
// --- NUEVOS MODELOS PARA CATÁLOGOS DINÁMICOS ---
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
            'configuraciones' => function($query) {
                $query->with(['producto', 'horasProduccion'])->orderBy('created_at', 'asc');
            }
        ]);

        return Inertia::render('Produccion/Inyeccion/RegistroHorario', [
            'turno' => $turno,
            'productos' => Producto::where('area', 'LIKE', '%INYECCIÓN%')
                ->orderBy('descripcion')
                ->get(['id', 'item', 'descripcion', 'ciclo', 'cavidades']),
            
            // --- INYECCIÓN DE DATOS QUIRÚRGICA ---
            'materiales' => MateriaPrima::orderBy('nombre')->get(), // Catálogo Materia Prima
            
            'pncCatalogo' => PncCatalogo::whereIn('area', ['Inyección', 'Ambos'])
                ->orderBy('nombre')
                ->get(), // Catálogo de Defectos real
            
            'paradasCatalogo' => ParadaCatalogo::orderBy('codigo')->get(), // Catálogo de Fallas real
            
            'anomalias' => AnomaliaProduccion::all(), // Mantener por compatibilidad legacy
        ]);
    }

    public function guardarConfiguracion(Request $request, Turno $turno)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'color'       => 'nullable|string|max:50',
            'mezcla'      => 'required|array',
            // Opcional: Validar que cada materia_prima_id exista en la tabla
            'mezcla.*.materia_prima_id' => 'required|exists:materia_prima,id'
        ]);

        $turno->configuraciones()->create([
            'producto_id'         => $request->producto_id,
            'mezcla_materiales'   => [
                'color'      => strtoupper($request->color),
                'materiales' => $request->mezcla
            ],
        ]);

        $turno->unsetRelation('configActiva');
        $turno->unsetRelation('configuraciones');
        $turno->load(['configActiva.producto', 'configuraciones.producto', 'configuraciones.horasProduccion']);

        return back()->with('success', 'Referencia Iniciada');
    }

    public function guardarHora(Request $request)
    {
        $validated = $request->validate([
            'config_id'       => 'required|exists:produccion_config,id',
            'hora'            => 'required',
            'unidades_buenas' => 'required|integer|min:0',
            'num_vale'        => 'nullable|string',
            'pnc'             => 'nullable|array' 
        ]);

        ProduccionHoraria::updateOrCreate(
            [
                'config_id' => $validated['config_id'], 
                'hora'      => $validated['hora']
            ],
            [
                'unidades_buenas'     => $validated['unidades_buenas'], 
                'num_vale_inyectora'  => $validated['num_vale'], 
                'pnc_detalle'         => $validated['pnc'] ?? []
            ]
        );

        return back()->with('message', 'Hora e Inspección sincronizadas');
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
            'configuraciones.horasProduccion'
        ]);

        return inertia('Produccion/Inyeccion/Analisis', [
            'turno' => $turno
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
}