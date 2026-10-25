<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Cached, pre-encoded body of GET /api/v1/meals.
 *
 * The menu is identical for every customer and only changes when an admin
 * edits meals / extras / categories / groups, so we build it once, keep the
 * JSON (and a gzipped copy) in cache, and flush it on any of those changes.
 */
class MealMenuCache
{
    private const KEY = 'api:v1:meals:index';

    // Safety net only — normal invalidation is event-driven (see flush()).
    private const TTL_SECONDS = 3600;

    /**
     * @param  callable(): string  $buildJson
     * @return array{json: string, gzip: string|null, etag: string, generated_at: int}
     */
    public static function remember(callable $buildJson): array
    {
        return Cache::remember(self::KEY, self::TTL_SECONDS, function () use ($buildJson) {
            $json = $buildJson();

            return [
                'json'         => $json,
                'gzip'         => function_exists('gzencode') ? gzencode($json, 6) : null,
                'etag'         => md5($json),
                'generated_at' => time(),
            ];
        });
    }

    public static function flush(): void
    {
        Cache::forget(self::KEY);
    }
}
