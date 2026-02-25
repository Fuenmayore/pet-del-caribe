<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Produccion\ProduccionController; // Asegúrate de que esta ruta sea correcta
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



// --- RUTA DE BIENVENIDA ---
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// --- DASHBOARD GENERAL ---
// Nota: Quitamos 'verified' para que los operarios puedan entrar sin confirmar email
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

// --- RUTAS PROTEGIDAS (REQUIEREN LOGIN) ---
Route::middleware('auth')->group(function () {
    
    // 1. Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 2. Módulo de Producción - Inyección (Formato F1)
    Route::prefix('produccion/inyeccion')->name('produccion.')->group(function () {
        
        // Paso 1: Dashboard de máquinas (Selección)
        Route::get('/', [ProduccionController::class, 'index'])->name('index');
        
        // Paso 2: Configuración inicial del turno (Referencia, Cavidades)
        // Usamos 'preparar' para evitar conflicto con la función global config()
        Route::get('/config/{maquina}', [ProduccionController::class, 'config'])->name('config');
        
        // Paso 3: Guardar el inicio y crear el UUID del turno
        Route::post('/store', [ProduccionController::class, 'store'])->name('store');
        
        // Paso 4: El reporte horario (F1)
        // Usamos la función 'registro' del controlador para cargar datos del turno
        Route::get('/registro/{turno}', [ProduccionController::class, 'registro'])->name('registro');
    });

});

require __DIR__.'/auth.php';