<?php

use App\Http\Controllers\Settings\ConfiguracionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Produccion\ProduccionController;
use App\Http\Controllers\Settings\MaquinaController;
use App\Http\Controllers\Settings\ProductoController;
use App\Http\Controllers\Settings\UserController;
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
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

// --- RUTAS PROTEGIDAS ---
Route::middleware(['auth'])->group(function () {

    // 1. Gestión de Perfil (Breeze default)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 2. Grupo de Producción Inyección
    Route::prefix('produccion/inyeccion')->name('produccion.')->group(function () {
        Route::get('/', [ProduccionController::class, 'index'])->name('index');
        Route::get('/config/{maquina}', [ProduccionController::class, 'config'])->name('config');
        Route::post('/store', [ProduccionController::class, 'store'])->name('store');
        Route::get('/registro/{turno}', [ProduccionController::class, 'registro'])->name('registro');
        Route::post('/configurar/{turno}', [ProduccionController::class, 'guardarConfiguracion'])->name('configurar');
        Route::post('/guardar-hora', [ProduccionController::class, 'guardarHora'])->name('guardarHora');
        Route::post('/finalizar/{turno}', [ProduccionController::class, 'finalizar'])->name('finalizar');
        Route::delete('/{turno}', [ProduccionController::class, 'destroy'])->name('destroy');
    });

    // 3. GRUPO DE CONFIGURACIONES GLOBAL (Settings)
    Route::prefix('configuraciones')->name('settings.')->group(function () {
        
        // Panel Principal de Ajustes
        Route::get('/', [ConfiguracionController::class, 'index'])->name('index');

        // CRUD de Máquinas y Productos
        Route::resource('maquinas', MaquinaController::class);
        Route::resource('productos', ProductoController::class);

        /** * GESTIÓN UNIFICADA DE SEGURIDAD (UserController)
         * El recurso 'usuarios' crea: index, store, update, destroy.
         * El parámetro automático será {usuario} (coincidiendo con tu controlador).
         */
        Route::resource('usuarios', UserController::class);
        
        // Rutas manuales para la lógica de ROLES que vive dentro del mismo UserController
        Route::post('roles/save', [UserController::class, 'saveRole'])->name('roles.save');
        Route::delete('roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');

        // Placeholders para futuros módulos de Calidad y Mantenimiento
        Route::get('/anomalias', function() { return 'Anomalias'; })->name('anomalias.index');
        Route::get('/fallas', function() { return 'Fallas'; })->name('fallas.index');
    });

});

require __DIR__.'/auth.php';