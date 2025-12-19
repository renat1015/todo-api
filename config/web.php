<?php

use app\services\CacheServiceInterface;
use app\services\MemcachedCacheService;

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY'),
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
            'on beforeSend' => function ($event) {
                $response = $event->sender;

                if ($response->statusCode >= 500) {
                    Yii::error("Unhandled error: " . $response->data['message'] ?? 'Unknown error');

                    if (!YII_DEBUG) {
                        $response->data = [
                            'success' => false,
                            'message' => 'Internal server error',
                        ];
                    }
                }

                if ($response->statusCode >= 400 && isset($response->data['message'])) {
                    $response->data = [
                        'success' => false,
                        'message' => $response->data['message'],
                        'errors' => $response->data['errors'] ?? null,
                    ];
                }
            },
        ],
        'memcachedCache' => [
            'class' => 'yii\caching\MemCache',
            'useMemcached' => true,
            'servers' => [
                [
                    'host' => getenv('MEMCACHED_HOST'),
                    'port' => 11211,
                ],
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'api/task',
                    'pluralize' => false,
                ],
            ],
        ],
    ],
    'container' => [
        'definitions' => [
            CacheServiceInterface::class => MemcachedCacheService::class,
        ],
    ],
    'modules' => [
        'api' => [
            'class' => 'app\modules\api\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];

    if (file_exists(__DIR__ . '/web-local.php')) {
        $localConfig = require __DIR__ . '/web-local.php';
        $config = \yii\helpers\ArrayHelper::merge($config, $localConfig);
    }
}

return $config;
