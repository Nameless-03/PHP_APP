<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    /**
     * List all categories, auto-seeding defaults if the table is empty.
     */
    public function index(): JsonResponse
    {
        if (Categoria::count() === 0) {
            Categoria::insert([
                ['nombre' => 'Consultoría', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Educación', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Salud y Bienestar', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Tecnología', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Diseño y Creatividad', 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Otros', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        return response()->json([
            'data' => Categoria::all(),
        ]);
    }
}
