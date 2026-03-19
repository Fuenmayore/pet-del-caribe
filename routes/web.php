<?php

use App\Http\Controllers\Settings\ConfiguracionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Produccion\ProduccionController;
use App\Http\Controllers\Settings\ColorController;
use App\Http\Controllers\Settings\MaquinaController;
use App\Http\Controllers\Settings\MateriaPrimaController;
use App\Http\Controllers\Settings\ParadaSettingsController;
use App\Http\Controllers\Settings\ProductoController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\Settings\PncSettingsController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// --- RUTA DE BIENVENIDA ---
// Sustituye tu código anterior por este:
Route::get('/', function () {
    return redirect()->route('login');
});

// --- DASHBOARD GENERAL ---
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

// --- RUTAS PROTEGIDAS POR AUTENTICACIÓN ---
Route::middleware(['auth'])->group(function () {

    // 1. Gestión de Perfil (Cualquier usuario logueado puede editar su propio perfil)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 2. GRUPO DE PRODUCCIÓN INYECCIÓN
    Route::prefix('produccion/inyeccion')->name('produccion.')->group(function () {
        
        // Vistas base: No tienen middleware estricto porque dentro de la vista ocultamos cosas con $can()
        Route::get('/', [ProduccionController::class, 'index'])->name('index');
        Route::get('/registro/{turno}', [ProduccionController::class, 'registro'])->name('registro');
        Route::get('/config/{maquina}', [ProduccionController::class, 'config'])->name('config');

        // ACCIONES CRÍTICAS (Protegidas por Spatie)
        // Solo quienes tienen permiso de abrir turnos (Operario, Supervisor, Admin)
        Route::post('/store', [ProduccionController::class, 'store'])
            ->name('store')
            ->middleware('permission:turnos.abrir');
            
        // Solo quienes tienen permiso de finalizar (Operario, Supervisor, Admin)
        Route::post('/finalizar/{turno}', [ProduccionController::class, 'finalizar'])
            ->name('finalizar')
            ->middleware('permission:turnos.finalizar');

        // ¡PELIGRO! Solo Supervisores/Admins pueden destruir un turno por completo
        Route::delete('/{turno}', [ProduccionController::class, 'destroy'])
            ->name('destroy')
            ->middleware('permission:turnos.cancelar');

        // Solo Operarios/Supervisores/Admins pueden guardar o editar horas
        // Nota: Dentro del controlador podríamos validar si es edición o creación para separar permisos,
        // pero por ahora protegemos la ruta entera.
        Route::post('/guardar-hora', [ProduccionController::class, 'guardarHora'])
            ->name('guardarHora')
            ->middleware('permission:registro_horario.crear|registro_horario.editar');

        // Solo quienes pueden cambiar moldes
        Route::post('/configurar/{turno}', [ProduccionController::class, 'guardarConfiguracion'])
            ->name('configurar')
            ->middleware('permission:moldes.cambiar|moldes.corregir_historial');

        // PERFILES DE OPERACIÓN (Recetas)
        Route::get('/{turno}/perfil', [ProduccionController::class, 'crearPerfil'])
            ->name('crearPerfil')
            ->middleware('permission:perfiles.ver|perfiles.crear');
            
        Route::post('/{turno}/perfil', [ProduccionController::class, 'guardarPerfil'])
            ->name('guardarPerfil')
            ->middleware('permission:perfiles.crear');

        // ANÁLISIS OEE (Dashboard de resultados)
        Route::get('/analisis/{turno}', [ProduccionController::class, 'analisis'])
            ->name('analisis')
            ->middleware('permission:analisis.ver');
    });

    // 3. GRUPO DE CONFIGURACIONES GLOBAL (Settings)
    // Protegemos TODO el grupo de configuraciones para que solo entren quienes pueden "ver" o "gestionar" catálogos
    Route::prefix('configuraciones')->name('settings.')->middleware('permission:catalogos.ver|catalogos.gestionar')->group(function () {
        
        // Panel Principal de Ajustes
        Route::get('/', [ConfiguracionController::class, 'index'])->name('index');

        // CRUD de Máquinas y Productos
        // (En un futuro, podrías poner 'middleware' => 'permission:catalogos.gestionar' solo a los métodos POST/PUT/DELETE)
        Route::resource('maquinas', MaquinaController::class);
        Route::resource('productos', ProductoController::class);
        Route::resource('pnc', PncSettingsController::class);
        Route::resource('paradas', ParadaSettingsController::class);
        Route::resource('materiaprima', MateriaPrimaController::class);
        Route::resource('colores', ColorController::class);

        /** * GESTIÓN UNIFICADA DE SEGURIDAD (Solo ADMIN)
         */
        Route::middleware('role:Admin')->group(function () {
            Route::resource('usuarios', UserController::class);
            Route::post('roles/save', [UserController::class, 'saveRole'])->name('roles.save');
            Route::delete('roles/{role}', [UserController::class, 'destroyRole'])->name('roles.destroy');
        });

        // Placeholders para futuros módulos de Calidad y Mantenimiento
        Route::get('/anomalias', function() { return 'Anomalias'; })->name('anomalias.index');
        Route::get('/fallas', function() { return 'Fallas'; })->name('fallas.index');
    });

});

require __DIR__.'/auth.php';