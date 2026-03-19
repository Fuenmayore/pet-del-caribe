<?php
// app/Http/Controllers/Settings/ParadaSettingsController.php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ParadaCatalogo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParadaSettingsController extends Controller {
    public function index() {
        return Inertia::render('Settings/Paradas/Index', [
            'paradas' => ParadaCatalogo::orderBy('codigo')->get()
        ]);
    }

    public function store(Request $request) {
        $valid = $request->validate([
            'codigo' => 'required|unique:paradas_catalogo,codigo',
            'falla' => 'required|string',
            'categoria' => 'required|string'
        ]);
        ParadaCatalogo::create($valid);
        return back()->with('message', 'Código de falla creado');
    }

    // MÉTODO FALTANTE PARA ACTUALIZAR
    public function update(Request $request, ParadaCatalogo $parada) 
    {
        $valid = $request->validate([
            'codigo'    => 'required|unique:paradas_catalogo,codigo,' . $parada->id,
            'falla'     => 'required|string',
            'categoria' => 'required|string'
        ]);

        $parada->update($valid);

        return back()->with('message', 'Falla actualizada');
    }

    // MÉTODO FALTANTE QUE CAUSA EL ERROR (DESTROY)
    public function destroy(ParadaCatalogo $parada) 
    {
        $parada->delete(); // Esto ejecutará el SoftDelete que definimos en la Fase I

        return back()->with('message', 'Código eliminado');
    }

}