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
}
