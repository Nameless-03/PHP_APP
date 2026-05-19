<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterClienteRequest;
use App\Http\Requests\RegisterProfesionalRequest;
use App\Http\Resources\UsuarioResource;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /**
     * Handle user login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return response()->json([
            'message' => 'Login successful',
            'user' => new UsuarioResource($result['usuario']),
            'token' => $result['token'],
        ]);
    }

    /**
     * Handle Cliente registration.
     */
    public function registerCliente(RegisterClienteRequest $request): JsonResponse
    {
        $usuario = $this->authService->registerCliente($request->validated());

        return response()->json([
            'message' => 'Cliente registered successfully',
            'user' => new UsuarioResource($usuario),
        ], 201);
    }

    /**
     * Handle Profesional registration.
     */
    public function registerProfesional(RegisterProfesionalRequest $request): JsonResponse
    {
        $usuario = $this->authService->registerProfesional($request->validated());

        return response()->json([
            'message' => 'Profesional registered successfully',
            'user' => new UsuarioResource($usuario),
        ], 201);
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UsuarioResource($request->user()->load(['cliente', 'profesional', 'admin'])),
        ]);
    }
}
