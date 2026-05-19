<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $categorias = [
            ['nombre' => 'Desarrollo de Software', 'descripcion' => 'Programación, arquitectura de sistemas y más.', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Diseño Gráfico', 'descripcion' => 'Diseño de interfaces, logos, branding.', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Asesoría Legal', 'descripcion' => 'Consultoría legal para empresas e individuos.', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Consultoría Financiera', 'descripcion' => 'Asesoramiento en finanzas y contabilidad.', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Marketing Digital', 'descripcion' => 'SEO, SEM, redes sociales y publicidad.', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Psicología y Coaching', 'descripcion' => 'Apoyo psicológico y coaching profesional.', 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Otro', 'descripcion' => 'Otras categorías no listadas.', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('categorias')->insert($categorias);
    }
}
