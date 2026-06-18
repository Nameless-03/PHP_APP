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

    /**
     * Test de que al crear una reserva se notifica al cliente y al profesional.
     */
    public function test_creacion_de_reserva_notifica_a_cliente_y_profesional(): void
    {
        \Illuminate\Support\Facades\Notification::fake();

        $mañana = Carbon::tomorrow()->setHour(10)->setMinute(0)->toDateTimeString();

        $data = [
            'id_servicio' => $this->servicio->id,
            'fecha_hora_inicio' => $mañana,
            'observaciones' => 'Nota de prueba'
        ];

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->postJson('/api/reservas', $data);

        $response->assertStatus(201);

        // Verificar que el cliente recibió ReservaEstadoNotificacion
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $this->clienteUser,
            \App\Notifications\ReservaEstadoNotificacion::class
        );

        // Verificar que el profesional recibió ReservaEstadoNotificacion
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $this->profesionalUser,
            \App\Notifications\ReservaEstadoNotificacion::class
        );
    }

    /**
     * Test de que al confirmar una reserva no se duplican las notificaciones al cliente.
     */
    public function test_confirmacion_de_reserva_no_duplica_notificaciones_al_cliente(): void
    {
        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PENDIENTE,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        \Illuminate\Support\Facades\Notification::fake();

        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'confirmada'
            ]);

        $response->assertStatus(200);

        // Debe recibir ReservaConfirmadaNotification
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $this->clienteUser,
            \App\Notifications\ReservaConfirmadaNotification::class
        );

        // NO debe recibir la genérica ReservaEstadoNotificacion
        \Illuminate\Support\Facades\Notification::assertNotSentTo(
            $this->clienteUser,
            \App\Notifications\ReservaEstadoNotificacion::class
        );
    }

    /**
     * Test de que una reserva en estado en_curso bloquea la disponibilidad y previene solapamientos.
     */
    public function test_reserva_en_curso_bloquea_disponibilidad_y_previene_solapamiento(): void
    {
        $inicio = Carbon::tomorrow()->setHour(12)->setMinute(0);
        $fin = (clone $inicio)->addMinutes(60);

        // Crear una reserva en curso
        Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => $fin,
            'estado' => \App\Enums\EstadoReservaEnum::EN_CURSO,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        // Intentar reservar el mismo horario
        $data = [
            'id_servicio' => $this->servicio->id,
            'fecha_hora_inicio' => $inicio->toDateTimeString()
        ];

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->postJson('/api/reservas', $data);

        $response->assertStatus(422); // Debería fallar por solapamiento

        // Verificar además mediante el calculador de turnos
        $calculador = app(\App\Services\CalculadorTurnosService::class);
        
        // Crear disponibilidad base para el profesional (mañana)
        $nombresDias = [
            0 => 'domingo', 1 => 'lunes', 2 => 'martes', 3 => 'miercoles',
            4 => 'jueves', 5 => 'viernes', 6 => 'sabado'
        ];
        $diaSemana = $nombresDias[Carbon::tomorrow()->dayOfWeek];
        
        \App\Models\Disponibilidad::create([
            'dia_semana' => $diaSemana,
            'hora_inicio' => '08:00',
            'hora_fin' => '17:00',
            'id_profesional' => $this->profesionalUser->id
        ]);

        $turnos = $calculador->obtenerTurnosDisponibles($this->servicio, Carbon::tomorrow()->toDateString());
        
        // El horario '12:00' no debería figurar en los turnos disponibles
        $this->assertNotContains('12:00', $turnos);
    }

    /**
     * Test de que al reprogramar una reserva pagada desde el cliente se mantiene en estado PAGADA.
     */
    public function test_reprogramar_reserva_pagada_desde_cliente_mantiene_estado_pagada(): void
    {
        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PAGADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $nuevaFecha = Carbon::tomorrow()->setHour(14)->setMinute(0)->toDateTimeString();

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/reprogramar", [
                'fecha_hora_inicio' => $nuevaFecha
            ]);

        $response->assertStatus(200);
        $reservaFresh = $reserva->fresh();
        $this->assertEquals(\App\Enums\EstadoReservaEnum::PAGADA->value, $reservaFresh->estado->value);
        $this->assertEquals($nuevaFecha, $reservaFresh->fecha_hora_inicio->toDateTimeString());
    }

    /**
     * Test de que al reprogramar una reserva pagada desde el profesional se actualiza a CONFIRMADA.
     */
    public function test_reprogramar_reserva_pagada_desde_profesional_actualiza_a_confirmada(): void
    {
        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PAGADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $nuevaFecha = Carbon::tomorrow()->setHour(15)->setMinute(0)->toDateTimeString();

        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/reprogramar", [
                'fecha_hora_inicio' => $nuevaFecha
            ]);

        $response->assertStatus(200);
        $reservaFresh = $reserva->fresh();
        $this->assertEquals(\App\Enums\EstadoReservaEnum::CONFIRMADA->value, $reservaFresh->estado->value);
        $this->assertEquals($nuevaFecha, $reservaFresh->fecha_hora_inicio->toDateTimeString());
    }

    /**
     * Test de que al cancelar una reserva pagada se marca su pago como reembolsado.
     */
    public function test_cancelar_reserva_pagada_marca_pago_como_reembolsado(): void
    {
        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PAGADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $pago = \App\Models\Pago::create([
            'monto' => 50.00,
            'metodo' => 'paypal',
            'estado' => 'completado',
            'id_reserva' => $reserva->id
        ]);

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'cancelada'
            ]);

        $response->assertStatus(200);
        $pago->refresh();
        $this->assertEquals('reembolsado', $pago->estado);
    }

    /**
     * Test de que el comando de cancelación automática cancela las reservas reprogramadas no confirmadas a tiempo.
     */
    public function test_comando_cancela_automaticamente_reservas_reprogramadas_no_confirmadas_a_tiempo(): void
    {
        // 1. Configurar servicio con 10 horas de limite de cancelación
        $this->servicio->update(['limite_cancelacion_horas' => 10]);

        // 2. Crear una reserva pagada que comienza en 9 horas (ya pasó el límite de 10 horas antes del inicio)
        $inicio = Carbon::now()->addHours(9);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PAGADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        // 3. Simular que fue reprogramada por el cliente guardándolo en la caché
        \Illuminate\Support\Facades\Cache::put("reprogramada_por_cliente_{$reserva->id}", true, now()->addDays(1));

        // 4. Ejecutar el comando
        $this->artisan('turnos:cancelar-no-confirmados')
            ->expectsOutput("Cancelando automáticamente reserva #{$reserva->id} reprogramada y no confirmada a tiempo.")
            ->assertExitCode(0);

        // 5. Verificar que la reserva fue cancelada en la base de datos (eliminada lógicamente)
        $this->assertEquals(\App\Enums\EstadoReservaEnum::CANCELADA->value, \App\Models\Reserva::withTrashed()->find($reserva->id)->estado->value);
        
        // 6. Verificar que el flag de la caché fue limpiado
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has("reprogramada_por_cliente_{$reserva->id}"));
    }

    /**
     * Test de que se pueden marcar todas las notificaciones como leídas.
     */
    public function test_usuario_puede_marcar_todas_las_notificaciones_como_leidas(): void
    {
        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PENDIENTE,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $this->clienteUser->notify(new \App\Notifications\ReservaEstadoNotificacion(
            $reserva, "Titulo 1", "Mensaje 1"
        ));
        $this->clienteUser->notify(new \App\Notifications\ReservaEstadoNotificacion(
            $reserva, "Titulo 2", "Mensaje 2"
        ));

        $this->assertEquals(2, $this->clienteUser->unreadNotifications()->count());

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->patchJson('/api/auth/notificaciones/marcar-todas-leidas');

        $response->assertStatus(200);
        $this->assertEquals(0, $this->clienteUser->unreadNotifications()->count());
    }

    /**
     * Test de obtener la reserva actual para cliente (solo videollamadas).
     */
    public function test_obtener_reserva_actual_cliente_retorna_solo_videollamada(): void
    {
        // 1. Crear reserva remota activa ahora
        $inicio = Carbon::now()->subMinutes(10);
        $fin = Carbon::now()->addMinutes(50);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => $fin,
            'estado' => \App\Enums\EstadoReservaEnum::PAGADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->getJson('/api/reservas/actual');

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $reserva->id);

        // 2. Modificar modalidad a presencial y verificar que no la retorna para el cliente
        $this->servicio->update(['modalidad' => 'presencial']);
        
        $response2 = $this->actingAs($this->clienteUser, 'sanctum')
            ->getJson('/api/reservas/actual');
            
        $response2->assertStatus(200);
        $response2->assertJsonPath('data', null);
    }

    /**
     * Test de obtener la reserva actual para el profesional (incluye presencial).
     */
    public function test_obtener_reserva_actual_profesional_retorna_cualquier_modalidad(): void
    {
        $this->servicio->update(['modalidad' => 'presencial']);
        $inicio = Carbon::now()->subMinutes(10);
        $fin = Carbon::now()->addMinutes(50);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => $fin,
            'estado' => \App\Enums\EstadoReservaEnum::CONFIRMADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $response = $this->actingAs($this->profesionalUser, 'sanctum')
            ->getJson('/api/reservas/actual');

        $response->assertStatus(200);
        $response->assertJsonPath('data.id', $reserva->id);
    }

    /**
     * Test de que el cliente puede iniciar la reserva (transición a en_curso).
     */
    public function test_cliente_puede_iniciar_reserva_en_curso(): void
    {
        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::PAGADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->patchJson("/api/reservas/{$reserva->id}/estado", [
                'estado' => 'en_curso'
            ]);

        $response->assertStatus(200);
        $this->assertEquals(\App\Enums\EstadoReservaEnum::EN_CURSO->value, $reserva->fresh()->estado->value);
    }

    /**
     * Test del comando de auto-finalización con margen de 5 minutos.
     */
    public function test_comando_finaliza_reservas_expiradas_con_margen_de_5_minutos(): void
    {
        // 1. Reserva expirada hace 6 minutos (debe finalizar)
        $inicio1 = Carbon::now()->subMinutes(66);
        $fin1 = Carbon::now()->subMinutes(6);
        $reserva1 = Reserva::create([
            'fecha_hora_inicio' => $inicio1,
            'fecha_hora_fin' => $fin1,
            'estado' => \App\Enums\EstadoReservaEnum::EN_CURSO,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        // 2. Reserva expirada hace 3 minutos (no debe finalizar por el margen de 5 min)
        $inicio2 = Carbon::now()->subMinutes(63);
        $fin2 = Carbon::now()->subMinutes(3);
        $reserva2 = Reserva::create([
            'fecha_hora_inicio' => $inicio2,
            'fecha_hora_fin' => $fin2,
            'estado' => \App\Enums\EstadoReservaEnum::EN_CURSO,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        $this->artisan('turnos:finalizar-expirados')
            ->expectsOutput("Finalizando automáticamente reserva #{$reserva1->id} por expiración de tiempo (con margen de 5 min).")
            ->assertExitCode(0);

        $this->assertEquals(\App\Enums\EstadoReservaEnum::FINALIZADA->value, $reserva1->fresh()->estado->value);
        $this->assertEquals(\App\Enums\EstadoReservaEnum::EN_CURSO->value, $reserva2->fresh()->estado->value);
    }

    /**
     * Test de que ReservaResource incluye el flag calificada correctamente.
     */
    public function test_reserva_resource_incluye_flag_calificada(): void
    {
        $inicio = Carbon::tomorrow()->setHour(10)->setMinute(0);
        $reserva = Reserva::create([
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->addMinutes(60),
            'estado' => \App\Enums\EstadoReservaEnum::FINALIZADA,
            'id_cliente' => $this->clienteUser->id,
            'id_servicio' => $this->servicio->id
        ]);

        // 1. Verificar calificada = false
        $response = $this->actingAs($this->clienteUser, 'sanctum')
            ->getJson("/api/reservas/{$reserva->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('data.calificada', false);

        // 2. Crear calificacion
        \App\Models\Calificacion::create([
            'puntuacion' => 5,
            'comentario' => 'Excelente servicio',
            'id_reserva' => $reserva->id
        ]);

        // 3. Verificar calificada = true
        $response2 = $this->actingAs($this->clienteUser, 'sanctum')
            ->getJson("/api/reservas/{$reserva->id}");
        $response2->assertStatus(200);
        $response2->assertJsonPath('data.calificada', true);
    }
}

