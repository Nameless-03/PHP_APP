<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Profesional;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\Paquete;
use App\Models\CompraPaquete;
use App\Models\Pago;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CompraPaqueteTest extends TestCase
{
    use RefreshDatabase;

    private $cliente;
    private $usuarioCliente;
    private $profesional;
    private $usuarioPro;
    private $paquete;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear Cliente
        $this->usuarioCliente = Usuario::create([
            'nombre' => 'Carlos Cliente',
            'email' => 'carlos@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::CLIENTE,
        ]);
        $this->cliente = Cliente::create([
            'id_usuario' => $this->usuarioCliente->id,
            'telefono' => '123456789',
        ]);

        // Crear Profesional
        $this->usuarioPro = Usuario::create([
            'nombre' => 'Ana Profesional',
            'email' => 'ana@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::PROFESIONAL,
        ]);
        $this->profesional = Profesional::create([
            'id_usuario' => $this->usuarioPro->id,
        ]);

        // Crear Categoria y Servicio
        $categoria = Categoria::create(['nombre' => 'Salud']);
        $servicio = Servicio::create([
            'nombre' => 'Terapia Física',
            'descripcion' => 'Sesión de terapia',
            'precio' => 50.00,
            'modalidad' => 'presencial',
            'duracion' => 45,
            'id_profesional' => $this->usuarioPro->id,
            'id_categoria' => $categoria->id,
        ]);

        // Crear Paquete
        $this->paquete = Paquete::create([
            'nombre' => 'Paquete Salud 5',
            'descripcion' => 'Cinco sesiones de salud',
            'cantidad_sesiones' => 5,
            'precio' => 200.00,
            'id_profesional' => $this->usuarioPro->id,
        ]);
        $this->paquete->servicios()->sync([$servicio->id]);
    }

    /**
     * Test de compra exitosa de un paquete.
     */
    public function test_cliente_puede_comprar_paquete_exitosamente(): void
    {
        $payload = [
            'metodo' => 'paypal',
        ];

        $response = $this->actingAs($this->usuarioCliente, 'sanctum')
            ->postJson("/api/paquetes/{$this->paquete->id}/comprar", $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'sesiones_disponibles',
                    'fecha_compra',
                    'estado',
                    'id_cliente',
                    'id_paquete',
                    'paquete',
                ]
            ])
            ->assertJsonPath('data.sesiones_disponibles', 5)
            ->assertJsonPath('data.estado', 'activo');

        $this->assertDatabaseHas('compras_paquete', [
            'id_cliente' => $this->usuarioCliente->id,
            'id_paquete' => $this->paquete->id,
            'sesiones_disponibles' => 5,
            'estado' => 'activo',
        ]);

        $compra = CompraPaquete::latest()->first();

        $this->assertDatabaseHas('pagos', [
            'monto' => 200.00,
            'metodo' => 'paypal',
            'estado' => 'completado',
            'id_compra' => $compra->id,
        ]);
    }

    /**
     * Test de compra fallida por simulación de error en el pago.
     */
    public function test_cliente_no_puede_comprar_si_se_simula_error(): void
    {
        $payload = [
            'metodo' => 'transferencia',
            'simular_error' => true,
        ];

        $response = $this->actingAs($this->usuarioCliente, 'sanctum')
            ->postJson("/api/paquetes/{$this->paquete->id}/comprar", $payload);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Error en el procesamiento del pago. Operación cancelada.');

        $this->assertDatabaseCount('compras_paquete', 0);
        $this->assertDatabaseCount('pagos', 0);
    }

    /**
     * Test de validaciones de campos requeridos.
     */
    public function test_validaciones_de_campos_requeridos_en_la_compra(): void
    {
        $response = $this->actingAs($this->usuarioCliente, 'sanctum')
            ->postJson("/api/paquetes/{$this->paquete->id}/comprar", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['metodo']);
    }

    /**
     * Test que impide a profesionales comprar paquetes.
     */
    public function test_solo_clientes_pueden_realizar_compras_de_paquetes(): void
    {
        $payload = [
            'metodo' => 'paypal',
        ];

        $response = $this->actingAs($this->usuarioPro, 'sanctum')
            ->postJson("/api/paquetes/{$this->paquete->id}/comprar", $payload);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'Forbidden. Requires cliente role.');
    }

    /**
     * Test de listado de paquetes adquiridos por el cliente.
     */
    public function test_cliente_puede_listar_sus_paquetes_adquiridos(): void
    {
        $compra = CompraPaquete::create([
            'sesiones_disponibles' => 5,
            'fecha_compra' => now(),
            'estado' => 'activo',
            'id_cliente' => $this->usuarioCliente->id,
            'id_paquete' => $this->paquete->id,
        ]);

        $response = $this->actingAs($this->usuarioCliente, 'sanctum')
            ->getJson('/api/mis-paquetes');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $compra->id)
            ->assertJsonPath('data.0.paquete.nombre', 'Paquete Salud 5');
    }
}
