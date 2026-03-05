<?php

namespace App\Http\Controllers\Produccion;

use App\Http\Controllers\Controller;
use App\Models\Turno;
use App\Models\Producto;
use App\Models\Maquina;
use App\Models\ProduccionHoraria;
use App\Models\AnomaliaProduccion;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ProduccionController extends Controller
{
    /**
     * Dashboard Principal.
     * Orden: 1. Activos, 2. Libres, 3. Historial (Paginado)
     */
    public function index(Request $request)
{
    return Inertia::render('Produccion/Inyeccion/Index', [
        // 1. MÁQUINAS LIBRES (Para iniciar turno)
        // Solo máquinas de inyección que no tengan turnos abiertos
        'maquinasLibres' => Maquina::where('sub_area', 'Inyeccion')
            ->whereDoesntHave('turnos', function ($query) {
                $query->where('estado', 'Abierto');
            })->get(),

        // 2. TURNOS ACTIVOS DEL USUARIO
        // Cargamos la config activa y todas las configuraciones para detectar cambios de referencia
        'misTurnosActivos' => Turno::with(['maquina', 'configActiva.producto', 'configuraciones'])
            ->where('operario_id', auth()->id())
            ->where('estado', 'Abierto')
            ->get(),

        // 3. HISTORIAL GENERAL (Paginado para eficiencia)
        // Agregamos 'operario' para mostrar quién trabajó cada turno
        'historialTurnos' => Turno::with([
                'maquina', 
                'operario', // <--- Agregamos la relación del usuario/operario
                'configuraciones.producto', 
                'configuraciones.horasProduccion'
            ])
            ->orderBy('fecha', 'desc')
            ->orderBy('numero_turno', 'desc')
            ->paginate(15) // Aumentamos a 15 para una mejor vista en tablas
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
            'anomalias' => AnomaliaProduccion::all(),
        ]);
    }

        public function guardarConfiguracion(Request $request, Turno $turno)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'color'       => 'nullable|string|max:50',
            'mezcla'      => 'required|array',
        ]);

        $turno->configuraciones()->create([
            'producto_id'         => $request->producto_id,
            // Guardamos el color y los materiales juntos en el JSON
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
        // Validamos los datos recibidos del frontend
        $validated = $request->validate([
            'config_id'       => 'required|exists:produccion_config,id',
            'hora'            => 'required',
            'unidades_buenas' => 'required|integer|min:0',
            'num_vale'        => 'nullable|string',
            // 'pnc' ahora incluye: cavidades_reales, ciclo_real, defectos, paros e inspeccion
            'pnc'             => 'nullable|array' 
        ]);

        // Guardamos o actualizamos el registro de la hora específica
        ProduccionHoraria::updateOrCreate(
            [
                'config_id' => $validated['config_id'], 
                'hora'      => $validated['hora']
            ],
            [
                'unidades_buenas'     => $validated['unidades_buenas'], 
                'num_vale_inyectora'  => $validated['num_vale'], 
                // Se guarda el objeto completo (incluyendo la inspección de preformas) en la columna JSON
                'pnc_detalle'         => $validated['pnc'] ?? []
            ]
        );

        return back()->with('message', 'Hora e Inspección de Preformas sincronizadas');
    }

    /**
     * FINALIZAR TURNO.
     * Cambia el estado para que pase al historial y libere la máquina.
     */
    public function finalizar(Turno $turno)
    {
        $turno->update(['estado' => 'Cerrado']);
        return redirect()->route('produccion.index')->with('message', 'Turno finalizado con éxito');
    }

    /**
     * ELIMINAR REGISTRO (Soft Delete).
     */
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