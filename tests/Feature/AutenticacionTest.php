<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AutenticacionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test client registration.
     */
    public function test_un_cliente_puede_registrarse_correctamente(): void
    {
        $data = [
            'nombre' => 'Juan Perez',
            'email' => 'juan@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'telefono' => '099123456',
            'foto_perfil' => 'https://example.com/avatar.jpg'
        ];

        $response = $this->postJson('/api/auth/register/cliente', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'nombre',
                    'email',
                    'role',
                    'fecha_registro',
                    'cliente' => [
                        'telefono',
                        'foto_perfil'
                    ]
                ]
            ])
            ->assertJsonPath('user.email', 'juan@example.com')
            ->assertJsonPath('user.role', 'cliente');

        $this->assertDatabaseHas('usuarios', [
            'email' => 'juan@example.com',
            'role' => 'cliente'
        ]);

        $this->assertDatabaseHas('clientes', [
            'telefono' => '099123456'
        ]);
    }

    /**
     * Test professional registration.
     */
    public function test_un_profesional_puede_registrarse_correctamente(): void
    {
        $data = [
            'nombre' => 'Dr. House',
            'email' => 'house@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'descripcion' => 'Medico especialista',
            'experiencia' => '15 anos de experiencia',
            'ubicacion' => 'Montevideo',
            'modalidad_preferida' => 'remota'
        ];

        $response = $this->postJson('/api/auth/register/profesional', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => [
                    'id',
                    'nombre',
                    'email',
                    'role',
                    'fecha_registro',
                    'profesional' => [
                        'descripcion',
                        'experiencia',
                        'ubicacion',
                        'modalidad_preferida',
                        'reputacion'
                    ]
                ]
            ])
            ->assertJsonPath('user.email', 'house@example.com')
            ->assertJsonPath('user.role', 'profesional');

        $this->assertDatabaseHas('usuarios', [
            'email' => 'house@example.com',
            'role' => 'profesional'
        ]);

        $this->assertDatabaseHas('profesionales', [
            'descripcion' => 'Medico especialista',
            'modalidad_preferida' => 'remota'
        ]);
    }

    /**
     * Test user login with correct credentials.
     */
    public function test_un_usuario_puede_iniciar_sesion_con_credenciales_correctas(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Jose',
            'email' => 'jose@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::CLIENTE,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jose@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user',
                'token'
            ]);

        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Test user login with incorrect credentials.
     */
    public function test_un_usuario_no_puede_iniciar_sesion_con_credenciales_incorrectas(): void
    {
        Usuario::create([
            'nombre' => 'Jose',
            'email' => 'jose@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::CLIENTE,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'jose@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test authenticated user retrieval.
     */
    public function test_un_usuario_autenticado_puede_obtener_su_perfil(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Jose',
            'email' => 'jose@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::CLIENTE,
        ]);

        $response = $this->actingAs($usuario, 'sanctum')
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'nombre',
                    'email',
                    'role',
                ]
            ])
            ->assertJsonPath('user.email', 'jose@example.com');
    }

    /**
     * Test logout and token revocation.
     */
    public function test_un_usuario_autenticado_puede_cerrar_sesion(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Jose',
            'email' => 'jose@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::CLIENTE,
        ]);

        $token = $usuario->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully'
            ]);

        $this->assertCount(0, $usuario->fresh()->tokens);
    }

    /**
     * Test role-based middleware access control.
     */
    public function test_un_administrador_puede_acceder_a_rutas_de_administracion(): void
    {
        $admin = Usuario::create([
            'nombre' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::ADMIN,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/usuarios');

        $response->assertStatus(200);
    }

    public function test_un_cliente_no_puede_acceder_a_rutas_de_administracion(): void
    {
        $cliente = Usuario::create([
            'nombre' => 'Cliente User',
            'email' => 'cliente@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::CLIENTE,
        ]);

        $response = $this->actingAs($cliente, 'sanctum')
            ->getJson('/api/usuarios');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Forbidden. Requires admin role.'
            ]);
    }

    public function test_un_usuario_no_autenticado_no_puede_acceder_a_rutas_protegidas(): void
    {
        $response = $this->getJson('/api/usuarios');

        $response->assertStatus(401);
    }

    /**
     * Test google callback redirection when user already exists.
     */
    public function test_google_callback_redirecciona_correctamente_cuando_el_usuario_existe(): void
    {
        // Creamos el usuario simulado con el email de mock_google
        $usuario = Usuario::create([
            'nombre' => 'Usuario Google Mock',
            'email' => 'mock_google@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::CLIENTE,
            'google_id' => 'google_mock_123456',
        ]);

        // Mock del cliente asociado
        \App\Models\Cliente::create([
            'id_usuario' => $usuario->id,
            'telefono' => '099000000',
        ]);

        // Hacemos el GET al callback con el code mock
        $response = $this->get('/api/auth/google/callback?code=mock_code');

        // Debería redirigir al login del frontend con ?google_auth=1
        $response->assertStatus(302);
        
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/login', $location);
        $this->assertStringContainsString('google_auth=1', $location);

        // El token y los datos del usuario se envían como cookies (no en la URL)
        $response->assertCookieNotExpired('google_auth_token');
        $response->assertCookieNotExpired('google_auth_user');

        // Verificamos que el cookie del usuario tenga la estructura correcta
        $userCookieRaw = $response->getCookie('google_auth_user', false);
        if ($userCookieRaw) {
            $userData = json_decode(urldecode($userCookieRaw->getValue()), true);
            $this->assertArrayHasKey('id', $userData);
            $this->assertArrayNotHasKey('data', $userData);
            $this->assertEquals('mock_google@example.com', $userData['email']);
            $this->assertEquals('cliente', $userData['role']);
        }
    }

    /**
     * Test register cliente with google_id returns token immediately.
     */
    public function test_registro_de_cliente_con_google_retorna_token_de_inmediato(): void
    {
        $data = [
            'nombre' => 'Juan Google',
            'email' => 'juan_google@example.com',
            'google_id' => 'google_mock_112233',
            'telefono' => '099112233',
            'foto_perfil' => 'https://example.com/avatar.jpg'
        ];

        $response = $this->postJson('/api/auth/register/cliente', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'nombre', 'email', 'role'],
                'token'
            ])
            ->assertJsonPath('user.email', 'juan_google@example.com');

        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Test register profesional with google_id returns token immediately.
     */
    public function test_registro_de_profesional_con_google_retorna_token_de_inmediato(): void
    {
        $data = [
            'nombre' => 'House Google',
            'email' => 'house_google@example.com',
            'google_id' => 'google_mock_445566',
            'descripcion' => 'Especialista',
            'experiencia' => '10 anos',
            'ubicacion' => 'Uruguay',
            'modalidad_preferida' => 'presencial'
        ];

        $response = $this->postJson('/api/auth/register/profesional', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'nombre', 'email', 'role'],
                'token'
            ])
            ->assertJsonPath('user.email', 'house_google@example.com');

        $this->assertNotEmpty($response->json('token'));
    }
}
