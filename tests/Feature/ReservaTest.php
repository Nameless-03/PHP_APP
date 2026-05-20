<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Cliente;
use App\Models\Profesional;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\Reserva;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Carbon\Carbon;

class ReservaTest extends TestCase
{
    use RefreshDatabase;

    private $clienteUser;
    private $profesionalUser;
    private $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear profesional
        $this->profesionalUser = Usuario::create([
            'nombre' => 'Profesional Test',
            'email' => 'pro@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::PROFESIONAL,
        ]);

        Profesional::create([
            'id_usuario' => $this->profesionalUser->id,
            'descripcion' => 'Descripción profesional',
            'experiencia' => 'Experiencia profesional',
            'ubicacion' => 'Ubicacion',
            'modalidad_preferida' => 'remota'
        ]);

        // 2. Crear categoria y servicio
        $categoria = Categoria::create(['nombre' => 'Salud']);
        $this->servicio = Servicio::create([
            'nombre' => 'Servicio Test',
            'descripcion' => 'Descripcion servicio',
            'duracion' => 60,
            'precio' => 50.00,
            'modalidad' => 'remota',
            'id_categoria' => $categoria->id,
            'id_profesional' => $this->profesionalUser->id
        ]);

        // 3. Crear cliente
        $this->clienteUser = Usuario::create([
            'nombre' => 'Cliente Test',
            'email' => 'cliente@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::CLIENTE,
        ]);

        Cliente::create([
            'id_usuario' => $this->clienteUser->id,
            'telefono' => '099123456'
        ]);
    }

    /**
     * Test de creación exitosa de reserva.
     */
    public function test_cliente_puede_realizar_reserva_correctamente(): void
    {
        // Petición para mañana a las 10:00 AM para evitar "isPast"
        $mañana = Carbon::tomorrow()->setHour(10)->setMinute(0)->toDateTimeString();

        $data = [
            'id_servicio' => $this->servicio->id,
            'fecha_hora_inicio' => $mañana,
            'observaciones' => 'Nota de prueba'
        ];

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->postJson('/api/reservas', $data);

        // Verificamos si devuelve 201
        $response->assertStatus(201);

        $this->assertDatabaseHas('reservas', [
            'id_servicio' => $this->servicio->id,
            'id_cliente' => $this->clienteUser->id,
            'fecha_hora_inicio' => $mañana
        ]);
    }

    /**
     * Test de validación cuando se reserva en el pasado.
     */
    public function test_no_se_puede_reservar_en_el_pasado(): void
    {
        $ayer = Carbon::yesterday()->toDateTimeString();

        $data = [
            'id_servicio' => $this->servicio->id,
            'fecha_hora_inicio' => $ayer
        ];

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->postJson('/api/reservas', $data);

        // Debería fallar con 422 por la validación
        $response->assertStatus(422);
    }

    /**
     * Test de creación exitosa de reserva consumiendo una sesión de paquete.
     */
    public function test_cliente_puede_reservar_consumiendo_sesion_de_paquete_exitosamente(): void
    {
        $mañana = Carbon::tomorrow()->setHour(10)->setMinute(0)->toDateTimeString();

        $paquete = \App\Models\Paquete::create([
            'nombre' => 'Paquete Test',
            'descripcion' => 'Un paquete de prueba',
            'cantidad_sesiones' => 5,
            'precio' => 150.00,
            'vencimiento' => 30,
            'id_profesional' => $this->profesionalUser->id
        ]);
        
        $paquete->servicios()->attach($this->servicio->id);

        $compra = \App\Models\CompraPaquete::create([
            'sesiones_disponibles' => 5,
            'estado' => 'activo',
            'id_cliente' => $this->clienteUser->id,
            'id_paquete' => $paquete->id
        ]);

        $data = [
            'id_servicio' => $this->servicio->id,
            'fecha_hora_inicio' => $mañana,
            'id_compra_paquete' => $compra->id,
            'observaciones' => 'Reserva con paquete'
        ];

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->postJson('/api/reservas', $data);

        $response->assertStatus(201);

        // Validar decremento de sesión
        $compra->refresh();
        $this->assertEquals(4, $compra->sesiones_disponibles);
        $this->assertEquals('activo', $compra->estado);

        // Validar que la reserva se creó como pagada y asociada al paquete
        $this->assertDatabaseHas('reservas', [
            'id_servicio' => $this->servicio->id,
            'id_cliente' => $this->clienteUser->id,
            'id_compra_paquete' => $compra->id,
            'estado' => \App\Enums\EstadoReservaEnum::PAGADA->value,
        ]);
    }

    /**
     * Test de validación cuando el paquete está agotado.
     */
    public function test_no_se_puede_reservar_con_paquete_agotado(): void
    {
        $mañana = Carbon::tomorrow()->setHour(10)->setMinute(0)->toDateTimeString();

        $paquete = \App\Models\Paquete::create([
            'nombre' => 'Paquete Test',
            'descripcion' => 'Un paquete de prueba',
            'cantidad_sesiones' => 5,
            'precio' => 150.00,
            'vencimiento' => 30,
            'id_profesional' => $this->profesionalUser->id
        ]);
        
        $paquete->servicios()->attach($this->servicio->id);

        $compra = \App\Models\CompraPaquete::create([
            'sesiones_disponibles' => 0,
            'estado' => 'agotado',
            'id_cliente' => $this->clienteUser->id,
            'id_paquete' => $paquete->id
        ]);

        $data = [
            'id_servicio' => $this->servicio->id,
            'fecha_hora_inicio' => $mañana,
            'id_compra_paquete' => $compra->id
        ];

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->postJson('/api/reservas', $data);

        $response->assertStatus(422);
    }

    /**
     * Test de validación cuando el servicio no pertenece al paquete.
     */
    public function test_no_se_puede_reservar_con_paquete_si_servicio_no_pertenece_al_paquete(): void
    {
        $mañana = Carbon::tomorrow()->setHour(10)->setMinute(0)->toDateTimeString();

        $paquete = \App\Models\Paquete::create([
            'nombre' => 'Paquete Diferente',
            'descripcion' => 'Otro paquete',
            'cantidad_sesiones' => 5,
            'precio' => 150.00,
            'vencimiento' => 30,
            'id_profesional' => $this->profesionalUser->id
        ]);
        
        // NO asociamos $this->servicio a este paquete

        $compra = \App\Models\CompraPaquete::create([
            'sesiones_disponibles' => 5,
            'estado' => 'activo',
            'id_cliente' => $this->clienteUser->id,
            'id_paquete' => $paquete->id
        ]);

        $data = [
            'id_servicio' => $this->servicio->id,
            'fecha_hora_inicio' => $mañana,
            'id_compra_paquete' => $compra->id
        ];

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->postJson('/api/reservas', $data);

        $response->assertStatus(422);
    }

    /**
     * Test de reembolso automático cuando una reserva con paquete se cancela.
     */
    public function test_cancelacion_de_reserva_con_paquete_reembolsa_sesion_correctamente(): void
    {
        $mañana = Carbon::tomorrow()->setHour(10)->setMinute(0);

        $paquete = \App\Models\Paquete::create([
            'nombre' => 'Paquete Test',
            'descripcion' => 'Un paquete de prueba',
            'cantidad_sesiones' => 5,
            'precio' => 150.00,
            'vencimiento' => 30,
            'id_profesional' => $this->profesionalUser->id
        ]);
        
        $paquete->servicios()->attach($this->servicio->id);

        // Simulamos que tenía 0 sesiones (agotado)
        $compra = \App\Models\CompraPaquete::create([
            'sesiones_disponibles' => 0,
            'estado' => 'agotado',
            'id_cliente' => $this->clienteUser->id,
            'id_paquete' => $paquete->id
        ]);

        $reserva = \App\Models\Reserva::create([
            'fecha_hora_inicio' => $mañana,
            'fecha_hora_fin' => (clone $mañana)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PAGADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id,
            'id_compra_paquete' => $compra->id
        ]);

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->patchJson('/api/reservas/' . $reserva->id . '/estado', [
                'estado' => 'cancelada'
            ]);

        $response->assertStatus(200);

        // Validar reembolso y reactivación del paquete
        $compra->refresh();
        $this->assertEquals(1, $compra->sesiones_disponibles);
        $this->assertEquals('activo', $compra->estado);
    }
}

