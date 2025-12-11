<?php

namespace app\services;

use Yii;
use yii\base\Component;
use yii\caching\CacheInterface;

/**
 * Service for managing cache
 */
class CacheService extends Component
{
    const KEY_TASKS_LIST = 'tasks_list_all';

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->cache = Yii::$app->cache;
    }

    /**
     * Get data from cache or query from db and set
     * 
     * @param string $key Cache key
     * @param callable $callable Callable function
     * @param int $duration Cache duration
     * @return mixed
     */
    public function getOrSet($key, callable $callable, $duration = null)
    {
        if ($duration === null) {
            $duration = Yii::$app->params['cacheDuration'] ?? 300;
        }

        return $this->cache->getOrSet($key, $callable, $duration);
    }

    /**
     * Clear tasks cache
     */
    public function clearTasksCache()
    {
        $this->cache->delete(self::KEY_TASKS_LIST);
    }
    
}
