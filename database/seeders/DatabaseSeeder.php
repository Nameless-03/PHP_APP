<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategoriaSeeder::class,
        ]);
$categorias = [
            [
                'nombre' => 'Desarrollo Web & IT',
                'descripcion' => 'Servicios de programación, desarrollo de software, diseño de sistemas y soporte técnico.',
            ],
            [
                'nombre' => 'Diseño & Creatividad',
                'descripcion' => 'Diseño gráfico, ilustración, edición de video, fotografía y branding.',
            ],
            [
                'nombre' => 'Marketing & Ventas',
                'descripcion' => 'SEO, publicidad online, redes sociales, redacción de contenidos y estrategias de ventas.',
            ],
            [
                'nombre' => 'Consultoría & Negocios',
                'descripcion' => 'Asesoría legal, contabilidad, mentorías de negocios y recursos humanos.',
            ],
            [
                'nombre' => 'Idiomas & Educación',
                'descripcion' => 'Clases de idiomas, apoyo escolar, preparación de exámenes y tutorías virtuales.',
            ],
            [
                'nombre' => 'Salud & Bienestar',
                'descripcion' => 'Nutrición, entrenamiento personal, coaching de vida y terapias de bienestar.',
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(['nombre' => $categoria['nombre']], $categoria);
        }
    }
}
