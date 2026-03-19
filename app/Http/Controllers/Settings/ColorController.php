<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ColorController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Colores/Index', [
            'colores' => Color::orderBy('nombre')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:colores,nombre'
        ]);

        Color::create([
            'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
            'activo' => true
        ]);

        return back()->with('message', 'Color registrado correctamente.');
    }

    // Cambiamos 'Color $color' por '$id' para evitar el bug del spanglish
    public function update(Request $request, $id)
    {
        // Buscamos el color manualmente
        $color = Color::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255|unique:colores,nombre,' . $color->id,
            'activo' => 'required|boolean'
        ]);

        $color->update([
            'nombre' => mb_strtoupper($request->nombre, 'UTF-8'),
            'activo' => $request->activo
        ]);

        return back()->with('message', 'Color actualizado correctamente.');
    }

    // Cambiamos 'Color $color' por '$id'
    public function destroy($id)
    {
        $color = Color::findOrFail($id);
        
        $color->delete();
        
        return back()->with('message', 'Color eliminado del sistema.');
    }
}