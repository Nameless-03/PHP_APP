<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profesional;
use Illuminate\Http\JsonResponse;

class PublicProfesionalController extends Controller
{
    /**
     * List all active professionals.
     */
    public function index(): JsonResponse
    {
        $profesionales = Profesional::whereHas('usuario', function ($q) {
            $q->where('activo', true);
        })
        ->with('usuario')
        ->get()
        ->map(function ($p) {
            return [
                'id_usuario' => $p->id_usuario,
                'nombre' => $p->usuario->nombre,
                'email' => $p->usuario->email,
                'descripcion' => $p->descripcion,
                'experiencia' => $p->experiencia,
                'ubicacion' => $p->ubicacion,
                'modalidad_preferida' => $p->modalidad_preferida,
                'reputacion' => (float) $p->reputacion,
                'foto_perfil' => $p->foto_perfil,
            ];
        });

        return response()->json([
            'data' => $profesionales
        ]);
    }

    /**
     * Show detailed profile of a single professional with their active services.
     */
    public function show(int $id): JsonResponse
    {
        $profesional = Profesional::where('id_usuario', $id)
            ->whereHas('usuario', function ($q) {
                $q->where('activo', true);
            })
            ->with(['usuario', 'servicios' => function ($q) {
                $q->where('activo', true);
            }])
            ->firstOrFail();

        return response()->json([
            'data' => [
                'id_usuario' => $profesional->id_usuario,
                'nombre' => $profesional->usuario->nombre,
                'email' => $profesional->usuario->email,
                'descripcion' => $profesional->descripcion,
                'experiencia' => $profesional->experiencia,
                'ubicacion' => $profesional->ubicacion,
                'telefono' => $profesional->telefono,
                'modalidad_preferida' => $profesional->modalidad_preferida,
                'reputacion' => (float) $profesional->reputacion,
                'foto_perfil' => $profesional->foto_perfil,
                'servicios' => $profesional->servicios->map(function ($s) {
                    return [
                        'id' => $s->id,
                        'nombre' => $s->nombre,
                        'descripcion' => $s->descripcion,
                        'precio' => (float) $s->precio,
                        'modalidad' => $s->modalidad?->value ?? $s->modalidad,
                        'duracion' => $s->duracion,
                        'ubicacion' => $s->ubicacion,
                    ];
                }),
            ]
        ]);
    }
}
