<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Profesional;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\Paquete;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PaqueteTest extends TestCase
{
    use RefreshDatabase;

    private $profesional1;
    private $usuarioPro1;
    private $profesional2;
    private $usuarioPro2;
    private $servicioPro1;
    private $servicioPro2;

    protected function setUp(): void
    {
        parent::setUp();

        // Profesional 1
        $this->usuarioPro1 = Usuario::create([
            'nombre' => 'Juan Perez',
            'email' => 'juan@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::PROFESIONAL,
        ]);
        $this->profesional1 = Profesional::create([
            'id_usuario' => $this->usuarioPro1->id,
            'reputacion' => 5.0,
        ]);

        // Profesional 2
        $this->usuarioPro2 = Usuario::create([
            'nombre' => 'Maria Gomez',
            'email' => 'maria@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::PROFESIONAL,
        ]);
        $this->profesional2 = Profesional::create([
            'id_usuario' => $this->usuarioPro2->id,
            'reputacion' => 4.5,
        ]);

        // Categoría
        $categoria = Categoria::create(['nombre' => 'Consultoría']);

        // Servicios
        $this->servicioPro1 = Servicio::create([
            'nombre' => 'Servicio Juan 1',
            'descripcion' => 'Descripción 1',
            'precio' => 100.00,
            'modalidad' => 'remota',
            'duracion' => 60,
            'id_profesional' => $this->usuarioPro1->id,
            'id_categoria' => $categoria->id,
        ]);

        $this->servicioPro2 = Servicio::create([
            'nombre' => 'Servicio Maria 1',
            'descripcion' => 'Descripción 2',
            'precio' => 150.00,
            'modalidad' => 'presencial',
            'duracion' => 90,
            'id_profesional' => $this->usuarioPro2->id,
            'id_categoria' => $categoria->id,
        ]);
    }

    /**
     * Test de creación exitosa de un paquete con servicios propios.
     */
    public function test_profesional_puede_crear_paquete_con_servicios_propios(): void
    {
        $data = [
            'nombre' => 'Paquete Premium Juan',
            'descripcion' => 'Un paquete increíble',
            'cantidad_sesiones' => 5,
            'precio' => 400.00,
            'vencimiento' => 30,
            'servicios' => [$this->servicioPro1->id],
        ];

        $response = $this->actingAs($this->usuarioPro1, 'sanctum')
            ->postJson('/api/paquetes', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'nombre',
                    'descripcion',
                    'cantidad_sesiones',
                    'precio',
                    'vencimiento',
                    'id_profesional',
                    'servicios' => [
                        '*' => ['id', 'nombre']
                    ]
                ]
            ])
            ->assertJsonPath('data.nombre', 'Paquete Premium Juan')
            ->assertJsonPath('data.precio', 400)
            ->assertJsonCount(1, 'data.servicios');

        $this->assertDatabaseHas('paquetes', [
            'nombre' => 'Paquete Premium Juan',
            'id_profesional' => $this->usuarioPro1->id,
        ]);

        $paquete = Paquete::latest()->first();
        $this->assertDatabaseHas('paquete_servicio', [
            'id_paquete' => $paquete->id,
            'id_servicio' => $this->servicioPro1->id,
        ]);
    }

    /**
     * Test que impide crear un paquete con servicios ajenos.
     */
    public function test_profesional_no_puede_crear_paquete_con_servicios_ajenos(): void
    {
        $data = [
            'nombre' => 'Paquete Ilícito',
            'descripcion' => 'Intentando robar servicios',
            'cantidad_sesiones' => 5,
            'precio' => 300.00,
            'vencimiento' => 30,
            'servicios' => [$this->servicioPro2->id], // Servicio de María
        ];

        $response = $this->actingAs($this->usuarioPro1, 'sanctum')
            ->postJson('/api/paquetes', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['servicios.0']);
    }

    /**
     * Test de validaciones de campos requeridos y tipos.
     */
    public function test_validaciones_de_campos_requeridos_y_tipos(): void
    {
        $data = [
            'nombre' => '',
            'cantidad_sesiones' => -1,
            'precio' => -50,
            'vencimiento' => -5,
            'servicios' => [],
        ];

        $response = $this->actingAs($this->usuarioPro1, 'sanctum')
            ->postJson('/api/paquetes', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['nombre', 'cantidad_sesiones', 'precio', 'vencimiento', 'servicios']);
    }

    /**
     * Test de listado de paquetes.
     */
    public function test_profesional_puede_listar_sus_propios_paquetes(): void
    {
        $paquete = Paquete::create([
            'nombre' => 'Mi Paquete 1',
            'descripcion' => 'Desc',
            'cantidad_sesiones' => 10,
            'precio' => 800.00,
            'vencimiento' => 60,
            'id_profesional' => $this->usuarioPro1->id,
        ]);
        $paquete->servicios()->sync([$this->servicioPro1->id]);

        $response = $this->actingAs($this->usuarioPro1, 'sanctum')
            ->getJson('/api/paquetes');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Mi Paquete 1');
    }

    /**
     * Test de eliminación de paquete propio.
     */
    public function test_profesional_puede_eliminar_su_propio_paquete(): void
    {
        $paquete = Paquete::create([
            'nombre' => 'Paquete a Borrar',
            'descripcion' => 'Desc',
            'cantidad_sesiones' => 10,
            'precio' => 800.00,
            'vencimiento' => 60,
            'id_profesional' => $this->usuarioPro1->id,
        ]);

        $response = $this->actingAs($this->usuarioPro1, 'sanctum')
            ->deleteJson("/api/paquetes/{$paquete->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('paquetes', ['id' => $paquete->id]);
    }

    /**
     * Test que impide eliminar paquete ajeno.
     */
    public function test_profesional_no_puede_eliminar_paquete_ajeno(): void
    {
        $paquete = Paquete::create([
            'nombre' => 'Paquete de Juan',
            'descripcion' => 'Desc',
            'cantidad_sesiones' => 10,
            'precio' => 800.00,
            'vencimiento' => 60,
            'id_profesional' => $this->usuarioPro1->id,
        ]);

        $response = $this->actingAs($this->usuarioPro2, 'sanctum')
            ->deleteJson("/api/paquetes/{$paquete->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('paquetes', ['id' => $paquete->id]);
    }
}
