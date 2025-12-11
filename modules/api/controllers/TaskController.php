<?php

namespace app\modules\api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\Task;
use yii\data\ActiveDataProvider;
use app\services\CacheService;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class TaskController extends ActiveController
{
    public $modelClass = 'app\models\Task';

    private $cacheService;

    public function init()
    {
        parent::init();
        $this->cacheService = Yii::$container->get(CacheService::class);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * Get task list
     * 
     * @return ActiveDataProvider
     */
    public function actionIndex()
    {
        return $this->cacheService->getOrSet(
            CacheService::KEY_TASKS_LIST,
            function() {
                return new ActiveDataProvider([
                    'query' => Task::find(),
                    'sort' => [
                        'defaultOrder' => [
                            'created_at' => SORT_DESC,
                        ],
                        'attributes' => ['id', 'title', 'description', 'status', 'created_at', 'updated_at'],
                    ],
                ]);
            }
        );
    }

    /**
     * View task
     * 
     * @param int $id
     * @return Task
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Create task
     * 
     * @return Task|array
     */
    public function actionCreate()
    {
        $model = new Task();

        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            $this->cacheService->clearTasksCache();
            Yii::$app->response->setStatusCode(201);
            return $model;
        }
        
        Yii::$app->response->setStatusCode(422);
        return ['errors' => $model->errors];
    }

    /**
     * Update task
     * 
     * @param int $id
     * @return Task|array
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
            $this->cacheService->clearTasksCache();
            return $model;
        }
        
        Yii::$app->response->setStatusCode(422);
        return ['errors' => $model->errors];
    }

    /**
     * Delete task
     * 
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if ($model->delete()) {
            $this->cacheService->clearTasksCache();
            Yii::$app->response->setStatusCode(204);
            return [];
        }
        
        Yii::$app->response->setStatusCode(500);
        return ['error' => 'Failed to delete task.'];
    }

    /**
     * Find model by ID
     * 
     * @param int $id
     * @return Task
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Task::findOne($id);
        
        if ($model === null) {
            throw new NotFoundHttpException('Task not found.');
        }
        
        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index'], $actions['view'], $actions['create'], 
            $actions['update'], $actions['delete']);
        
        return $actions;
    }

}
