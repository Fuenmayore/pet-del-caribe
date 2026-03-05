<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Settings/Usuarios/Index', [
            'users' => User::with('roles')->orderBy('nombre')->get(),
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'codigo_empleado' => 'required|string|max:50|unique:users,codigo_empleado',
            'email'           => 'required|string|email|max:255|unique:users',
            'password'        => ['required', 'confirmed', Rules\Password::defaults()],
            'rol'             => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'nombre'          => $request->nombre,
            'codigo_empleado' => $request->codigo_empleado,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'activo'          => true,
        ]);

        // Asignación inicial
        $user->assignRole($request->rol);

        return back()->with('message', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'codigo_empleado' => 'required|string|max:50|unique:users,codigo_empleado,' . $usuario->id,
            'email'           => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'rol'             => 'required|exists:roles,name',
            'activo'          => 'required|boolean',
            'password'        => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        // 1. Actualizar datos básicos
        $usuario->update([
            'nombre'          => $request->nombre,
            'codigo_empleado' => $request->codigo_empleado,
            'email'           => $request->email,
            'activo'          => $request->activo,
        ]);

        // 2. Actualizar contraseña solo si se escribió una nueva
        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        // 3. ACTUALIZACIÓN DEL ROL (Clave del éxito)
        // syncRoles elimina todos los anteriores y deja solo el que viene del formulario
        $usuario->syncRoles($request->rol);

        return back()->with('message', 'Usuario actualizado correctamente.');
    }

    /**
     * Eliminar Usuario (resource destroy)
     */
    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();
        return back()->with('message', 'Usuario eliminado correctamente.');
    }

    /**
     * Eliminar Rol (método manual destroyRole)
     */
    public function destroyRole(Role $role)
    {
        if ($role->name === 'ADMINISTRADOR') {
            return back()->with('error', 'No se puede eliminar el rol principal.');
        }

        $role->delete();
        return back()->with('message', 'Rol eliminado correctamente.');
    }

    public function saveRole(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'permissions' => 'array'
        ]);
        
        $role = Role::updateOrCreate(
            ['id' => $request->id],
            ['name' => $request->name, 'guard_name' => 'web']
        );

        $role->syncPermissions($request->permissions);

        return back()->with('message', 'Rol y permisos actualizados correctamente.');
    }
}