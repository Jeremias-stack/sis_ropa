<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{
    /**
     * Iniciar sesión en la API.
     */
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'correo' => [
                    'required',
                    'email',
                ],

                'password' => [
                    'required',
                    'string',
                ],
            ]
        );


        if ($validator->fails()) {

            return response()->json([
                'ok' => false,
                'message' => 'Datos de autenticación inválidos.',
                'errors' => $validator->errors(),
            ], 422);

        }


        $usuario = User::where(
            'correo',
            $request->correo
        )->first();


        if (
            !$usuario ||
            !Hash::check(
                $request->password,
                $usuario->password
            )
        ) {

            return response()->json([
                'ok' => false,
                'message' => 'Correo o contraseña incorrectos.',
            ], 401);

        }


        /*
         * Eliminar tokens anteriores.
         */
        $usuario->tokens()->delete();


        /*
         * Crear un nuevo token con Sanctum.
         */
        $token = $usuario
            ->createToken('pos-ropa-api')
            ->plainTextToken;


        return response()->json([
            'ok' => true,

            'message' =>
                'Autenticación correcta.',

            'token' =>
                $token,

            'usuario' => [
                'id' =>
                    $usuario->id,

                'nombre' =>
                    $usuario->nombre,

                'correo' =>
                    $usuario->correo,

                'rol' =>
                    $usuario->rol,
            ],
        ], 200);
    }


    /**
     * Cerrar sesión de la API.
     */
    public function logout(Request $request)
    {
        $token = $request
            ->user()
            ->currentAccessToken();


        if ($token) {

            $token->delete();

        }


        return response()->json([
            'ok' => true,

            'message' =>
                'Sesión API cerrada correctamente.',
        ], 200);
    }
}