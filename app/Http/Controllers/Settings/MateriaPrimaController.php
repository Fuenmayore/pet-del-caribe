<?php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\MateriaPrima;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MateriaPrimaController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/MateriaPrima/Index', [
            'materiales' => MateriaPrima::orderBy('nombre')->get()
        ]);
    }

    public function store(Request $request)
    {
        $valid = $request->validate([
            'nombre' => 'required|string|max:255|unique:materia_prima,nombre',
            'tipo' => 'required|in:Resina,Preforma,Pigmento,Aditivo,Otro',
            'unidad_medida' => 'required|string|max:10',
            'proveedor' => 'nullable|string|max:255'
        ]);

        MateriaPrima::create($valid);
        return back();
    }

    public function update(Request $request, MateriaPrima $materiaprima) 
    {
        $valid = $request->validate([
            'nombre' => 'required|string|max:255|unique:materia_prima,nombre,' . $materiaprima->id,
            'tipo' => 'required|in:Resina,Preforma,Pigmento,Aditivo,Otro',
            'unidad_medida' => 'required|string|max:10',
            'proveedor' => 'nullable|string|max:255'
        ]);

        $materiaprima->update($valid);
        return back();
    }

    public function destroy(MateriaPrima $materiaprima)
    {
        $materiaprima->delete(); // Ejecuta SoftDelete para auditoría [cite: 23]
        return back();
    }
}