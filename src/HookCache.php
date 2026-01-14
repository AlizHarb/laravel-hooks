<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

/**
 * Handles caching of compiled hooks for production performance.
 */
class HookCache
{
    protected string $cacheKey;
    protected bool $enabled;
    protected string $store;

    /**
     * Create a new HookCache instance.
     */
    public function __construct()
    {
        $this->enabled = Config::get('hooks.cache.enabled', false);
        $this->cacheKey = Config::get('hooks.cache.key', 'laravel_hooks_map');
        $this->store = Config::get('hooks.cache.store', 'file');
    }

    /**
     * Retrieve cached hooks.
     *
     * @return array|null
     */
    public function get(): ?array
    {
        if (! $this->enabled) {
            return null;
        }

        return Cache::store($this->store)->get($this->cacheKey);
    }

    /**
     * Store hooks in cache.
     *
     * @param array $hooks
     * @return void
     */
    public function put(array $hooks): void
    {
        Cache::store($this->store)->forever($this->cacheKey, $hooks);
    }

    /**
     * clear the hook cache.
     *
     * @return void
     */
    public function forget(): void
    {
        Cache::store($this->store)->forget($this->cacheKey);
    }
}
