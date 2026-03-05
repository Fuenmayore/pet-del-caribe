<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
   // En su ProductoController.php
public function index(Request $request) {
    $search = $request->input('search');
    $productos = Producto::when($search, function ($query, $search) {
        $query->where('item', 'like', "%{$search}%")
              ->orWhere('descripcion', 'like', "%{$search}%");
    })->paginate(15)->withQueryString(); // withQueryString es vital para no perder el filtro al cambiar de página

    return Inertia::render('Settings/Productos/Index', [
        'productos' => $productos,
        'filters' => ['search' => $search] // Opcional: para devolver el valor al input
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item'        => 'required|string|max:50|unique:productos,item',
            'descripcion' => 'required|string|max:255',
            'ciclo'       => 'required|numeric|min:0',
            'cavidades'   => 'required|integer|min:1',
            'area'        => 'required|string',
        ]);

        Producto::create($validated);

        return back()->with('message', 'Producto registrado correctamente');
    }

    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'item'        => 'required|string|max:50|unique:productos,item,' . $producto->id,
            'descripcion' => 'required|string|max:255',
            'ciclo'       => 'required|numeric|min:0',
            'cavidades'   => 'required|integer|min:1',
            'area'        => 'required|string',
        ]);

        $producto->update($validated);

        return back()->with('message', 'Producto actualizado');
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();
        return back()->with('message', 'Producto eliminado');
    }
}