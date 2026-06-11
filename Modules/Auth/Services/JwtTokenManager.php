<?php

declare(strict_types=1);

namespace Modules\Auth\Services;

use Illuminate\Support\Str;
use Modules\Auth\Contracts\JwtTokenManagerContract;
use Modules\Auth\DTOs\AccessTokenClaimsDTO;
use Modules\Auth\Enums\UserRole;
use Modules\Auth\Exceptions\InvalidTokenException;
use Modules\Auth\Models\User;

class JwtTokenManager implements JwtTokenManagerContract
{
    public function createAccessToken(User $user): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + (int) config('jwt.access_token_ttl', 3600);

        $payload = [
            'iss' => (string) config('jwt.issuer'),
            'sub' => $user->uuid,
            'role' => $user->role->value,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'jti' => (string) Str::uuid(),
        ];

        return $this->encode($payload);
    }

    public function validateAccessToken(string $token): AccessTokenClaimsDTO
    {
        $payload = $this->decode($token);

        if (($payload['exp'] ?? 0) < time()) {
            throw InvalidTokenException::accessTokenInvalid();
        }

        $role = UserRole::tryFrom((string) ($payload['role'] ?? ''));

        if ($role === null) {
            throw InvalidTokenException::accessTokenInvalid();
        }

        return new AccessTokenClaimsDTO(
            subject: (string) $payload['sub'],
            role: $role,
            jti: (string) $payload['jti'],
            issuedAt: (int) $payload['iat'],
            expiresAt: (int) $payload['exp'],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function encode(array $payload): string
    {
        $header = $this->base64UrlEncode(json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT',
        ], JSON_THROW_ON_ERROR));

        $body = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $signature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$body}", $this->secret(), true),
        );

        return "{$header}.{$body}.{$signature}";
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $token): array
    {
        $segments = explode('.', $token);

        if (count($segments) !== 3) {
            throw InvalidTokenException::accessTokenInvalid();
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $segments;

        $expectedSignature = $this->base64UrlEncode(
            hash_hmac('sha256', "{$encodedHeader}.{$encodedPayload}", $this->secret(), true),
        );

        if (! hash_equals($expectedSignature, $encodedSignature)) {
            throw InvalidTokenException::accessTokenInvalid();
        }

        $header = json_decode($this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (! is_array($header) || ! is_array($payload)) {
            throw InvalidTokenException::accessTokenInvalid();
        }

        if (($header['alg'] ?? null) !== 'HS256') {
            throw InvalidTokenException::accessTokenInvalid();
        }

        if (($payload['iss'] ?? null) !== (string) config('jwt.issuer')) {
            throw InvalidTokenException::accessTokenInvalid();
        }

        return $payload;
    }

    private function secret(): string
    {
        $secret = (string) config('jwt.secret');

        if ($secret === '') {
            throw InvalidTokenException::accessTokenInvalid();
        }

        if (str_starts_with($secret, 'base64:')) {
            $decoded = base64_decode(substr($secret, 7), true);

            if ($decoded === false) {
                throw InvalidTokenException::accessTokenInvalid();
            }

            return $decoded;
        }

        return $secret;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;

        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);

        if ($decoded === false) {
            throw InvalidTokenException::accessTokenInvalid();
        }

        return $decoded;
    }
}
