<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductoController extends Controller
{
    /**
     * Lista de productos con búsqueda profesional
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $productos = Producto::query()
            ->when($search, function ($query, $search) {
                $query->where('item', 'like', "%{$search}%")
                      ->orWhere('descripcion', 'like', "%{$search}%")
                      ->orWhere('ref_maq', 'like', "%{$search}%")
                      ->orWhere('area', 'like', "%{$search}%");
            })
            ->orderBy('item', 'asc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Settings/Productos/Index', [
            'productos' => $productos,
            'filters'   => ['search' => $search]
        ]);
    }

    /**
     * Registro de nuevo producto con campos de ingeniería
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ref_maq'         => 'nullable|string|max:100',
            'item'            => 'required|string|max:50|unique:productos,item',
            'descripcion'     => 'required|string|max:255',
            'tipo_inventario' => 'nullable|string|max:100',
            'area'            => 'required|string|max:100',
            'centro_trabajo'  => 'nullable|string|max:100',
            'preforma'        => 'nullable|string|max:100',
            'maquina'         => 'nullable|string|max:100',
            'ciclo'           => 'required|numeric|min:0',
            'cavidades'       => 'required|integer|min:0',
            'bot_hora'        => 'nullable|integer|min:0',
            'unidad_empaque'  => 'nullable|string|max:255',
        ]);

        Producto::create($validated);

        return back()->with('message', 'Producto registrado correctamente en el catálogo maestro');
    }

    /**
     * Actualización de ficha técnica del producto
     */
    public function update(Request $request, Producto $producto)
    {
        $validated = $request->validate([
            'ref_maq'         => 'nullable|string|max:100',
            'item'            => 'required|string|max:50|unique:productos,item,' . $producto->id,
            'descripcion'     => 'required|string|max:255',
            'tipo_inventario' => 'nullable|string|max:100',
            'area'            => 'required|string|max:100',
            'centro_trabajo'  => 'nullable|string|max:100',
            'preforma'        => 'nullable|string|max:100',
            'maquina'         => 'nullable|string|max:100',
            'ciclo'           => 'required|numeric|min:0',
            'cavidades'       => 'required|integer|min:0',
            'bot_hora'        => 'nullable|integer|min:0',
            'unidad_empaque'  => 'nullable|string|max:255',
        ]);

        $producto->update($validated);

        return back()->with('message', 'Ficha técnica actualizada correctamente');
    }

    /**
     * Eliminación (Soft Delete)
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();
        return back()->with('message', 'Producto enviado a la papelera');
    }
}