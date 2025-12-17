<?php

declare(strict_types=1);

namespace app\services;

/**
 * Interface for cache management
 */
interface CacheServiceInterface
{
    const KEY_TASKS_LIST = 'tasks_list_all';
    const DEFAULT_CACHE_DURATION = 300;

    /**
     * Get data from cache or query from db and set
     * 
     * @param string $key Cache key
     * @param callable $callable Callable function
     * @param int|null $duration Cache duration
     * @return mixed
     */
    public function getOrSet(string $key, callable $callable, ?int $duration = null): mixed;

    /**
     * Clear tasks cache
     */
    public function clearTasksCache(): void;
}
