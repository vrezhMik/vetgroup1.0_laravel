<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VetgroupUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function change(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'old_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6'],
            'confirm_password' => ['required', 'string', 'same:new_password'],
        ]);

        $token = $this->extractBearerToken((string) $request->header('Authorization', ''));

        if (! $token) {
            return response()->json([
                'message' => 'Missing or invalid authorization token.',
            ], 401);
        }

        $payload = $this->decodeJwt($token);

        if (! $payload || empty($payload['sub'])) {
            return response()->json([
                'message' => 'Invalid or expired token.',
            ], 401);
        }

        /** @var VetgroupUser|null $user */
        $user = VetgroupUser::query()->find($payload['sub']);

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $passwordOk = false;

        try {
            $passwordOk = Hash::check($validated['old_password'], $user->password);
        } catch (\RuntimeException) {
            $passwordOk = password_verify($validated['old_password'], (string) $user->password);

            if ($passwordOk) {
                $user->forceFill([
                    'password' => Hash::make($validated['old_password']),
                ])->save();
            }
        }

        if (! $passwordOk) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($validated['new_password']),
        ])->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    private function extractBearerToken(string $header): ?string
    {
        if (preg_match('/Bearer\s+(.+)/i', $header, $matches) === 1) {
            return trim($matches[1]);
        }

        return null;
    }

    private function decodeJwt(string $token): ?array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return null;
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = json_decode($this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);
        $signature = $this->base64UrlDecode($encodedSignature);

        if (! is_array($header) || ! is_array($payload) || $signature === null) {
            return null;
        }

        $expectedSignature = hash_hmac(
            'sha256',
            $encodedHeader.'.'.$encodedPayload,
            $this->jwtKey(),
            true,
        );

        if (! hash_equals($expectedSignature, $signature)) {
            return null;
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            return null;
        }

        return $payload;
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

    private function base64UrlDecode(string $data): ?string
    {
        $remainder = strlen($data) % 4;

        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($data, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}

