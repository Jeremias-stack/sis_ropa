<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UsuarioController extends Controller
{
    // Las rutas de este controlador están protegidas: solamente administrador.
    public function index(Request $request)
    {
        $usuarios = User::query()
            ->when($request->filled('buscar'), function ($query) use ($request) {
                $buscar = $request->string('buscar')->toString();
                $query->where(function ($q) use ($buscar) {
                    $q->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('correo', 'like', "%{$buscar}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(12)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'correo' => ['required', 'email', 'max:150', 'unique:users,correo'],
            'rol' => ['required', Rule::in(['cliente', 'cajero', 'administrador'])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'rol' => $datos['rol'],
            'password' => Hash::make($datos['password']),
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'correo' => ['required', 'email', 'max:150', Rule::unique('users', 'correo')->ignore($usuario->id)],
            'rol' => ['required', Rule::in(['cliente', 'cajero', 'administrador'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Evita perder el acceso administrativo al cambiar el propio rol.
        if ($usuario->id === auth()->id() && $datos['rol'] !== 'administrador') {
            throw ValidationException::withMessages(['rol' => 'No puedes quitarte el rol de administrador a ti mismo.']);
        }

        // Conserva por lo menos una cuenta administradora.
        if ($usuario->rol === 'administrador' && $datos['rol'] !== 'administrador'
            && User::where('rol', 'administrador')->count() <= 1) {
            throw ValidationException::withMessages(['rol' => 'Debe quedar al menos un administrador en el sistema.']);
        }

        $campos = [
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'rol' => $datos['rol'],
        ];
        if (!empty($datos['password'])) {
            $campos['password'] = Hash::make($datos['password']);
        }
        $usuario->update($campos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario y permisos actualizados.');
    }
}
