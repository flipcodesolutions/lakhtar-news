<?php

namespace App\Support;

class FirebaseCredentials
{
    /**
     * Resolve Firebase credentials from env or known storage paths.
     *
     * @return array<string, mixed>|string|null
     */
    public static function resolve(): array|string|null
    {
        $raw = env('FIREBASE_CREDENTIALS') ?: env('GOOGLE_APPLICATION_CREDENTIALS');

        if (is_string($raw) && str_starts_with(trim($raw), '{')) {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : null;
        }

        foreach (self::candidatePaths($raw) as $path) {
            if (file_exists($path)) {
                return realpath($path) ?: $path;
            }
        }

        return null;
    }

    /**
     * Prefer project_id from the credentials JSON so .env cannot silently
     * point FCM at an old/deleted Firebase project.
     */
    public static function resolveProjectId(?string $fallback = null): ?string
    {
        $credentials = self::resolve();

        if (is_string($credentials) && is_file($credentials)) {
            $decoded = json_decode((string) file_get_contents($credentials), true);
            $credentials = is_array($decoded) ? $decoded : null;
        }

        if (is_array($credentials) && ! empty($credentials['project_id'])) {
            return (string) $credentials['project_id'];
        }

        return $fallback;
    }

    /**
     * @return list<string>
     */
    protected static function candidatePaths(?string $raw): array
    {
        $paths = [
            storage_path('app/firebase/firebase_credentials.json'),
            base_path('storage/app/firebase/firebase_credentials.json'),
        ];

        if (is_string($raw) && $raw !== '') {
            $paths[] = $raw;

            if (! str_starts_with($raw, '/') && ! str_contains($raw, ':\\')) {
                $paths[] = base_path($raw);
            }
        }

        return array_values(array_unique($paths));
    }
}
