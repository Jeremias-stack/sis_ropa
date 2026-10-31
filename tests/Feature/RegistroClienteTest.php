<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_publico_crea_cliente_y_no_accede_al_pos(): void
    {
        $this->post(route('register.store'), [
            'nombre' => 'Cliente de prueba',
            'correo' => 'nuevo-cliente@example.test',
            'password' => 'clave-segura-123',
            'password_confirmation' => 'clave-segura-123',
        ])->assertRedirect(route('tienda.index'));

        $cliente = User::where('correo', 'nuevo-cliente@example.test')->firstOrFail();
        $this->assertSame('cliente', $cliente->rol);
        $this->assertAuthenticatedAs($cliente);
        $this->get(route('dashboard'))->assertForbidden();
    }

    public function test_registro_ignora_intentos_de_autoasignar_rol_de_empleado(): void
    {
        foreach (['cajero', 'administrador'] as $rolSolicitado) {
            // Cada petición utiliza un correo distinto; la autenticación debe
            // seguir siendo pública y el rol siempre debe ser cliente.
            auth()->logout();
            $correo = $rolSolicitado . '-intento@example.test';
            $this->post(route('register.store'), [
                'nombre' => 'Comprador de prueba',
                'correo' => $correo,
                'password' => 'clave-segura-123',
                'password_confirmation' => 'clave-segura-123',
                'rol' => $rolSolicitado,
            ])->assertRedirect(route('tienda.index'));

            $this->assertDatabaseHas('users', [
                'correo' => $correo,
                'rol' => 'cliente',
            ]);
        }
    }
}
