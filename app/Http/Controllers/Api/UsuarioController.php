<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UsuarioResource;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsuarioController extends Controller
{
    public function __construct(
        private UsuarioService $usuarioService
    ) {}

    /**
     * Display a listing of the users (Admin only).
     */
    public function index(): JsonResponse
    {
        $usuarios = $this->usuarioService->listarTodos();

        return response()->json([
            'data' => UsuarioResource::collection($usuarios),
        ]);
    }

    /**
     * Display the specified user.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        // Simple authorization check: user can only view themselves unless they are admin
        if (!$request->user()->esAdmin() && $request->user()->id !== $id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $usuario = $this->usuarioService->obtenerPorId($id);

        return response()->json([
            'data' => new UsuarioResource($usuario),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // Simple authorization check
        if (!$request->user()->esAdmin() && $request->user()->id !== $id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:usuarios,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'descripcion' => 'sometimes|nullable|string',
            'experiencia' => 'sometimes|nullable|string',
            'ubicacion' => 'sometimes|nullable|string',
            'modalidad_preferida' => 'sometimes|string|in:presencial,remota,hibrida',
            'foto_perfil' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        ]);

        $usuario = $this->usuarioService->obtenerPorId($id);

        if ($request->hasFile('foto_perfil')) {
            // Delete old photo if it exists
            if ($usuario->profesional && $usuario->profesional->foto_perfil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($usuario->profesional->foto_perfil);
            }
            $file = $request->file('foto_perfil');
            $path = $file->store('fotos_perfil', 'public');
            $validatedData['foto_perfil'] = $path;
        }

        $updatedUsuario = $this->usuarioService->actualizar($usuario, $validatedData);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UsuarioResource($updatedUsuario),
        ]);
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->usuarioService->eliminar($id);

        return response()->json([
            'message' => 'User deleted successfully',
        ], 204);
    }
}
