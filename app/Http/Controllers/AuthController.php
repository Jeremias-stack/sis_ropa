<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $datos = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $credenciales = [
            'correo' => $datos['correo'],
            'password' => $datos['password'],
        ];

        if (!Auth::attempt($credenciales, $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'correo' => 'El correo o la contraseña son incorrectos.',
                ])
                ->onlyInput('correo');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:150'],
            'correo' => ['required', 'email', 'max:150', 'unique:users,correo'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $usuario = User::create([
            'nombre' => $datos['nombre'],
            'correo' => $datos['correo'],
            'password' => Hash::make($datos['password']),
            'rol' => 'cajero',
        ]);

        Auth::login($usuario);

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Usuario registrado correctamente.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}