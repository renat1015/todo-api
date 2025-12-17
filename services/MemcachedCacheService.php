<?php

declare(strict_types=1);

namespace app\services;

use Yii;

/**
 * Service for managing cache
 */
class MemcachedCacheService extends AbstractCacheService
{
    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->cache = Yii::$app->memcachedCache;
    }
}
