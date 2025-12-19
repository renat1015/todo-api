<?php

declare(strict_types=1);

namespace app\services;

use Yii;
use yii\base\Component;
use yii\caching\CacheInterface;

/**
 * Abstract cache service with common functionality
 */
abstract class AbstractCacheService extends Component implements CacheServiceInterface
{
    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * Get data from cache or query from db and set
     * 
     * @param string $key Cache key
     * @param callable $callable Callable function
     * @param int|null $duration Cache duration
     * @return mixed
     */
    public function getOrSet(string $key, callable $callable, ?int $duration = null): mixed
    {
        if ($duration === null) {
            $duration = Yii::$app->params['cacheDuration'] ?? self::DEFAULT_CACHE_DURATION;
        }

        return $this->cache->getOrSet($key, $callable, $duration);
    }

    /**
     * Clear tasks cache
     */
    public function clearTasksCache(): void
    {
        $this->cache->delete(self::KEY_TASKS_LIST);
    }
}
