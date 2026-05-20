<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Profesional;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ServicioTest extends TestCase
{
    use RefreshDatabase;

    private $usuarioProfesional;
    private $profesional;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un usuario profesional de prueba
        $this->usuarioProfesional = Usuario::create([
            'nombre' => 'Dra. Ana Lopez',
            'email' => 'ana@example.com',
            'password' => Hash::make('password123'),
            'role' => RoleEnum::PROFESIONAL,
        ]);

        // Crear el perfil de profesional correspondiente
        $this->profesional = Profesional::create([
            'id_usuario' => $this->usuarioProfesional->id,
            'descripcion' => 'Especialista en Nutrición y Bienestar',
            'experiencia' => '8 años de práctica privada',
            'ubicacion' => 'Montevideo, Uruguay',
            'modalidad_preferida' => 'hibrida',
            'reputacion' => 5.0
        ]);
    }

    /**
     * Test de listado de categorías con auto-seeding.
     */
    public function test_categorias_se_autoseedan_y_se_listan_correctamente(): void
    {
        // Comprobar que inicialmente no hay categorías en la base de datos
        $this->assertEquals(0, Categoria::count());

        // Hacer la petición GET al endpoint público
        $response = $this->getJson('/api/categorias');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'nombre'
                    ]
                ]
            ]);

        // Debe haber insertado las 6 categorías por defecto
        $this->assertGreaterThan(0, Categoria::count());
        $this->assertDatabaseHas('categorias', ['nombre' => 'Consultoría']);
        $this->assertDatabaseHas('categorias', ['nombre' => 'Tecnología']);
    }

    /**
     * Test de creación de servicio con categoría existente.
     */
    public function test_profesional_puede_crear_servicio_con_categoria_existente(): void
    {
        // Forzar el auto-seeding visitando la ruta o creando una categoría
        $categoria = Categoria::create(['nombre' => 'Consultoría']);

        $data = [
            'nombre' => 'Asesoría Nutricional Personalizada',
            'descripcion' => 'Sesión completa de 1 hora para planificar tu dieta.',
            'duracion' => 60,
            'precio' => 75.00,
            'modalidad' => 'presencial',
            'id_categoria' => $categoria->id
        ];

        // Crear servicio autenticado como profesional
        $response = $this->actingAs($this->usuarioProfesional, 'sanctum')
            ->postJson('/api/servicios', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'nombre',
                    'descripcion',
                    'precio',
                    'duracion',
                    'modalidad',
                    'id_categoria',
                    'categoria' => [
                        'id',
                        'nombre'
                    ]
                ]
            ])
            ->assertJsonPath('data.nombre', 'Asesoría Nutricional Personalizada')
            ->assertJsonPath('data.id_categoria', $categoria->id);

        $this->assertDatabaseHas('servicios', [
            'nombre' => 'Asesoría Nutricional Personalizada',
            'id_categoria' => $categoria->id,
            'id_profesional' => $this->profesional->id_usuario
        ]);
    }

    /**
     * Test de creación de servicio con una nueva categoría en texto plano.
     */
    public function test_profesional_puede_crear_servicio_con_nueva_categoria_dinamica(): void
    {
        // Enviar un string como 'id_categoria'
        $data = [
            'nombre' => 'Clase de Yoga Vinyasa',
            'descripcion' => 'Clase dinámica para todos los niveles.',
            'duracion' => 45,
            'precio' => 30.00,
            'modalidad' => 'remota',
            'id_categoria' => 'Salud y Deporte'
        ];

        // Crear servicio autenticado como profesional
        $response = $this->actingAs($this->usuarioProfesional, 'sanctum')
            ->postJson('/api/servicios', $data);

        $response->assertStatus(201);

        // La categoría "Salud y Deporte" debió haberse creado en la base de datos
        $this->assertDatabaseHas('categorias', [
            'nombre' => 'Salud y Deporte'
        ]);

        $nuevaCategoria = Categoria::where('nombre', 'Salud y Deporte')->first();
        $this->assertNotNull($nuevaCategoria);

        // El servicio debió asociarse a la nueva categoría creada
        $this->assertDatabaseHas('servicios', [
            'nombre' => 'Clase de Yoga Vinyasa',
            'id_categoria' => $nuevaCategoria->id,
            'id_profesional' => $this->profesional->id_usuario
        ]);

        $response->assertJsonPath('data.id_categoria', $nuevaCategoria->id)
            ->assertJsonPath('data.categoria.nombre', 'Salud y Deporte');
    }
}
