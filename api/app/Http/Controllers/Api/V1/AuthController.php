<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthenticatedUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        return $this->tokenResponse(
            $user,
            $data['device_name'],
            $request,
            Response::HTTP_CREATED,
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais informadas são inválidas.'],
            ]);
        }

        return $this->tokenResponse($user, $credentials['device_name'], $request);
    }

    public function me(Request $request): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource(
            $request->user()->load('memberships.hotel'),
        );
    }

    public function logout(Request $request): Response
    {
        $accessToken = $request->user()->currentAccessToken();

        if ($accessToken instanceof PersonalAccessToken) {
            $accessToken->delete();
        }

        return response()->noContent();
    }

    private function tokenResponse(
        User $user,
        string $deviceName,
        Request $request,
        int $status = Response::HTTP_OK,
    ): JsonResponse {
        $expirationMinutes = (int) config('sanctum.expiration', 480);
        $expiresAt = now()->addMinutes($expirationMinutes);
        $token = $user->createToken($deviceName, ['api:access'], $expiresAt);

        return response()->json([
            'data' => [
                'user' => (new AuthenticatedUserResource(
                    $user->load('memberships.hotel'),
                ))->resolve($request),
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt->toIso8601String(),
            ],
        ], $status);
    }
}
