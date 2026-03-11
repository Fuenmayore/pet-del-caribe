<?php
// app/Http/Controllers/Settings/PncSettingsController.php
namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\PncCatalogo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PncSettingsController extends Controller {
    public function index() {
        return Inertia::render('Settings/Pnc/Index', [
            'catalogo' => PncCatalogo::orderBy('area')->get()
        ]);
    }

    public function store(Request $request) {
        $valid = $request->validate([
            'nombre' => 'required|string|max:255',
            'area' => 'required|in:Inyección,Soplado,Ambos'
        ]);
        PncCatalogo::create($valid);
        return back()->with('message', 'PNC agregado correctamente');
    }

    public function update(Request $request, PncCatalogo $pnc) {
        $valid = $request->validate([
            'nombre' => 'required|string|max:255',
            'area' => 'required|in:Inyección,Soplado,Ambos'
        ]);
        $pnc->update($valid);
        return back()->with('message', 'Actualizado con éxito');
    }

    public function destroy(PncCatalogo $pnc) {
        $pnc->delete(); // SoftDelete 
        return back()->with('message', 'Eliminado del catálogo');
    }
}