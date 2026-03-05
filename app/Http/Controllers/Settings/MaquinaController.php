<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Maquina;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaquinaController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Maquinas/Index', [
            // Solo trae las que no han sido "borradas"
            'maquinas' => Maquina::orderBy('nombre')->get() 
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:50|unique:maquinas,nombre',
            'sub_area' => 'required|in:Inyeccion,Soplado',
        ]);

        // El campo 'area' lo forzamos a PRODUCCIÓN por defecto si no viene
        Maquina::create([
            'nombre'   => $validated['nombre'],
            'sub_area' => $validated['sub_area'],
            'area'     => 'PRODUCCIÓN'
        ]);

        return back()->with('message', 'Máquina creada con éxito');
    }

    public function update(Request $request, Maquina $maquina)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:50|unique:maquinas,nombre,' . $maquina->id,
            'sub_area' => 'required|in:Inyeccion,Soplado',
        ]);

        $maquina->update($validated);

        return back()->with('message', 'Máquina actualizada');
    }

    public function destroy(Maquina $maquina)
    {
        // Al usar SoftDeletes en el modelo, esto solo llenará la columna deleted_at
        $maquina->delete();

        return back()->with('message', 'Máquina enviada a la papelera');
    }
}