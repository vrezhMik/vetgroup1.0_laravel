<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VetgroupUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identifier = $validated['identifier'];

        $user = VetgroupUser::query()
            ->when(
                filter_var($identifier, FILTER_VALIDATE_EMAIL),
                fn ($q) => $q->where('email', $identifier),
                fn ($q) => $q->where('username', $identifier),
            )
            ->first();

        $passwordOk = false;

        if ($user) {
            try {
                $passwordOk = Hash::check($validated['password'], $user->password);
            } catch (\RuntimeException) {
                $passwordOk = password_verify($validated['password'], (string) $user->password);

                if ($passwordOk) {
                    $user->forceFill([
                        'password' => Hash::make($validated['password']),
                    ])->save();
                }
            }
        }

        if (! $user || ! $passwordOk) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $documentId = (string) ($user->user_id ?: $user->id);
        $jwt = $this->makeJwt([
            'sub' => (string) $user->id,
            'documentId' => $documentId,
            'iat' => time(),
            'exp' => time() + 60 * 60 * 24 * 7,
        ]);

        return response()->json([
            'success' => true,
            'jwt' => $jwt,
            'token' => $jwt,
            'access_token' => $jwt,
            'documentId' => $documentId,
            'code' => $user->code,
            'company' => $user->company,
            'user' => [
                'id' => $user->id,
                'documentId' => $documentId,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'company' => $user->company,
                'location' => $user->location,
                'code' => $user->code,
                'user_id' => $user->user_id,
            ],
        ]);
    }

    private function makeJwt(array $payload): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];

        $encodedHeader = self::base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = self::base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));

        $signingInput = $encodedHeader . '.' . $encodedPayload;
        $signature = hash_hmac('sha256', $signingInput, $this->jwtKey(), true);
        $encodedSignature = self::base64UrlEncode($signature);

        return $signingInput . '.' . $encodedSignature;
    }

    private function jwtKey(): string
    {
        $key = (string) config('app.key', '');

        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            return $decoded === false ? $key : $decoded;
        }

        return $key;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
