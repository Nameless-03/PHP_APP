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
     * Test de creación de reserva consumiendo una sesión de paquete comprado en efectivo.
     */
    public function test_cliente_puede_reservar_consumiendo_sesion_de_paquete_en_efectivo(): void
    {
        $mañana = Carbon::tomorrow()->setHour(10)->setMinute(0)->toDateTimeString();

        $paquete = \App\Models\Paquete::create([
            'nombre' => 'Paquete Test Efectivo',
            'descripcion' => 'Un paquete de prueba en efectivo',
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

        // Crear pago asociado en efectivo
        \App\Models\Pago::create([
            'monto' => 150.00,
            'metodo' => 'efectivo',
            'estado' => \App\Enums\EstadoPagoEnum::PENDIENTE,
            'id_compra' => $compra->id
        ]);

        $data = [
            'id_servicio' => $this->servicio->id,
            'fecha_hora_inicio' => $mañana,
            'id_compra_paquete' => $compra->id,
            'observaciones' => 'Reserva con paquete en efectivo'
        ];

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->postJson('/api/reservas', $data);

        $response->assertStatus(201);

        $compra->refresh();
        $this->assertEquals(4, $compra->sesiones_disponibles);

        // Validar que la reserva se creó como pendiente y tiene un pago asociado
        $this->assertDatabaseHas('reservas', [
            'id_servicio' => $this->servicio->id,
            'id_cliente' => $this->clienteUser->id,
            'id_compra_paquete' => $compra->id,
            'estado' => \App\Enums\EstadoReservaEnum::PENDIENTE->value,
        ]);

        $reserva = \App\Models\Reserva::where('id_compra_paquete', $compra->id)->first();

        $this->assertDatabaseHas('pagos', [
            'id_reserva' => $reserva->id,
            'metodo' => 'efectivo',
            'estado' => \App\Enums\EstadoPagoEnum::PENDIENTE->value,
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

    /**
     * Test de validación de cancelación con límite dinámico de horas.
     */
    public function test_cliente_no_puede_cancelar_si_se_supera_el_limite_dinamico_del_servicio(): void
    {
        // 1. Configurar el servicio con límite de cancelación de 12 horas
        $this->servicio->update(['limite_cancelacion_horas' => 12]);

        // 2. Crear reserva para dentro de 11 horas (menos de las 12 horas requeridas)
        $inicio = Carbon::now()->addHours(11);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::CONFIRMADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        // 3. Intentar cancelar
        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'cancelada'
            ]);

        // Debería fallar con 422
        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Política de cancelación: No puedes cancelar con menos de 12 horas de anticipación.');
    }

    /**
     * Test de cancelación exitosa respetando el límite dinámico de horas.
     */
    public function test_cliente_puede_cancelar_si_esta_antes_del_limite_dinamico_del_servicio(): void
    {
        // 1. Configurar el servicio con límite de cancelación de 12 horas
        $this->servicio->update(['limite_cancelacion_horas' => 12]);

        // 2. Crear reserva para dentro de 13 horas (más de las 12 horas requeridas)
        $inicio = Carbon::now()->addHours(13);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::CONFIRMADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        // 3. Intentar cancelar
        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'cancelada'
            ]);

        // Debería tener éxito
        $response->assertStatus(200);
        $this->assertEquals(\App\Enums\EstadoReservaEnum::CANCELADA->value, \App\Models\Reserva::withTrashed()->find($reserva->id)->estado->value);
    }

    /**
     * Test de transiciones de estado válidas y prohibidas.
     */
    public function test_transiciones_de_estado_respetan_la_maquina_de_estados(): void
    {
        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PENDIENTE,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        // 1. PENDIENTE -> FINALIZADA (Invalida)
        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'finalizada'
            ]);
        $response->assertStatus(422);

        // 2. PENDIENTE -> CONFIRMADA (Valida)
        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'confirmada'
            ]);
        $response->assertStatus(200);
        $this->assertEquals(\App\Enums\EstadoReservaEnum::CONFIRMADA->value, $reserva->fresh()->estado->value);

        // 3. CONFIRMADA -> EN_CURSO (Valida)
        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'en_curso'
            ]);
        $response->assertStatus(200);
        $this->assertEquals(\App\Enums\EstadoReservaEnum::EN_CURSO->value, $reserva->fresh()->estado->value);

        // 4. EN_CURSO -> PENDIENTE (Invalida)
        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'pendiente'
            ]);
        $response->assertStatus(422);

        // 5. EN_CURSO -> FINALIZADA (Valida)
        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'finalizada'
            ]);
        $response->assertStatus(200);
        $this->assertEquals(\App\Enums\EstadoReservaEnum::FINALIZADA->value, $reserva->fresh()->estado->value);

        // 6. FINALIZADA -> CANCELADA (Invalida, terminal)
        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'cancelada'
            ]);
        $response->assertStatus(422);
    }

    /**
     * Test de que el admin puede gestionar el estado de las reservas.
     */
    public function test_administrador_puede_cambiar_estado_de_reserva(): void
    {
        $adminUser = Usuario::create([
            'nombre' => 'Admin Test',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::ADMIN,
        ]);

        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PENDIENTE,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $response = $this->actingAs($adminUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'confirmada'
            ]);

        $response->assertStatus(200);
        $this->assertEquals(\App\Enums\EstadoReservaEnum::CONFIRMADA->value, $reserva->fresh()->estado->value);
    }
}

