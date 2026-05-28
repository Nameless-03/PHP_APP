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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Usuario;
use App\Models\Cliente;
use App\Enums\RoleEnum;

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

    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(Request $request)
    {
        $clientId = env('GOOGLE_CLIENT_ID');
        $redirectUri = env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/api/auth/google/callback');

        if (empty($clientId) || $clientId === 'mock') {
            // In mock mode, immediately redirect to our callback endpoint with a mock code
            return redirect()->action([self::class, 'handleGoogleCallback'], ['code' => 'mock_code']);
        }

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => csrf_token(),
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    /**
     * Obtain the user information from Google.
     */
    public function handleGoogleCallback(Request $request)
    {
        $code = $request->query('code');
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

        if (!$code) {
            return redirect($frontendUrl . '/login?error=auth_failed');
        }

        $clientId = env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');
        $redirectUri = env('GOOGLE_REDIRECT_URI', 'http://localhost:8000/api/auth/google/callback');

        $googleUser = null;

        if ($code === 'mock_code' || empty($clientId) || $clientId === 'mock') {
            // Simulated user for local development and testing
            $googleUser = [
                'id' => 'google_mock_123456',
                'name' => 'Usuario Google Mock',
                'email' => 'mock_google@example.com',
                'picture' => 'https://lh3.googleusercontent.com/a/default-user=s96-c',
            ];
        } else {
            try {
                // Exchange code for token
                $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'code' => $code,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri' => $redirectUri,
                    'grant_type' => 'authorization_code',
                ]);

                if ($tokenResponse->failed()) {
                    throw new \Exception('Failed to retrieve access token from Google');
                }

                $accessToken = $tokenResponse->json()['access_token'];

                // Fetch user info
                $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

                if ($userResponse->failed()) {
                    throw new \Exception('Failed to retrieve user info from Google');
                }

                $googleUser = $userResponse->json();
                $googleUser['id'] = $googleUser['sub'] ?? '';
            } catch (\Exception $e) {
                logger()->error('Google OAuth Error: ' . $e->getMessage());
                return redirect($frontendUrl . '/login?error=google_auth_error');
            }
        }

        try {
            $usuario = DB::transaction(function () use ($googleUser) {
                $user = Usuario::where('email', $googleUser['email'])->first();

                if (!$user) {
                    // Create a new user with role "cliente" by default
                    $user = Usuario::create([
                        'nombre' => $googleUser['name'],
                        'email' => $googleUser['email'],
                        'password' => Hash::make(Str::random(16)),
                        'role' => RoleEnum::CLIENTE,
                        'fecha_registro' => now(),
                        'google_id' => $googleUser['id'],
                    ]);

                    // Create client record
                    Cliente::create([
                        'id_usuario' => $user->id,
                        'telefono' => null,
                        'foto_perfil' => $googleUser['picture'] ?? null,
                    ]);
                } else {
                    // Update google_id if missing
                    if (empty($user->google_id)) {
                        $user->google_id = $googleUser['id'];
                        $user->save();
                    }

                    // Update client picture if it exists and is empty
                    if ($user->esCliente() && $user->cliente) {
                        if (empty($user->cliente->foto_perfil) && !empty($googleUser['picture'])) {
                            $user->cliente->foto_perfil = $googleUser['picture'];
                            $user->cliente->save();
                        }
                    }
                }

                return $user->load(['cliente', 'profesional', 'admin']);
            });

            // Create Sanctum token
            $token = $usuario->createToken('auth_token')->plainTextToken;
            $userJson = json_encode(new UsuarioResource($usuario));
            $redirectUrl = $frontendUrl . '/login?token=' . urlencode($token) . '&user=' . urlencode($userJson);

            return redirect($redirectUrl);

        } catch (\Exception $e) {
            logger()->error('Google user login processing error: ' . $e->getMessage());
            return redirect($frontendUrl . '/login?error=database_error');
        }
    }
}
