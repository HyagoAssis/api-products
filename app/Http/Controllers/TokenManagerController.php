<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TokenManagerController extends Controller
{
    /**
     * Issue a new API token from HTTP Basic authentication credentials.
     *
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        $email = $request->getUser();
        $password = $request->getPassword();

        if (blank($email) || blank($password)) {
            throw ValidationException::withMessages([
                'authorization' => [__('auth.failed')],
            ]);
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'authorization' => [__('auth.failed')],
            ]);
        }

        return $this->handleTokenResponse($user, $request->input('token_name', 'api-token'));
    }

    /**
     * Build the token response for the given user.
     */
    protected function handleTokenResponse(User $user, string $tokenName): JsonResponse
    {
        $expiration = config('sanctum.expiration');

        $token = $user->createToken(
            $tokenName,
            ['*'],
            $expiration ? now()->addMinutes($expiration) : null,
        );

        return response()->json([
            'token' => $token->plainTextToken,
            'expires_at' => $token->accessToken->expires_at,
        ]);
    }
}
