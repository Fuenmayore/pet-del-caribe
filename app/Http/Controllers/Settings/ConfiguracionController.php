<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Maquina;
use App\Models\Producto;
use App\Models\MateriaPrima;
use App\Models\User;
use Inertia\Inertia;

class ConfiguracionController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Index', [
            'stats' => [
                'totalMaquinas' => Maquina::count(),
                'totalProductos' => Producto::count(),
                'totalUsuarios' => User::count(),
                'totalMateriaPrima' => MateriaPrima::count(),
            ]
        ]);
    }
}