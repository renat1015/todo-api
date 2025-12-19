<?php

declare(strict_types=1);

namespace app\modules\api\controllers;

use Yii;
use yii\rest\Controller;
use yii\base\UserException;
use yii\base\InvalidArgumentException;
use yii\web\NotFoundHttpException;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use app\models\Task;
use yii\data\ActiveDataProvider;
use app\services\CacheServiceInterface;
use app\traits\ApiResponseTrait;
use app\exceptions\ValidationException;
use app\exceptions\SaveModelException;

/**
 * TaskController implements the CRUD actions for Task model.
 */
class TaskController extends Controller
{
    use ApiResponseTrait;

    public $modelClass = 'app\models\Task';

    /**
     * @var CacheServiceInterface
     */
    private $cacheService;

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        parent::init();
        $this->cacheService = Yii::$container->get(CacheServiceInterface::class);
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
        try {
            $data = $this->cacheService->getOrSet(
                CacheServiceInterface::KEY_TASKS_LIST,
                function() {
                    return new ActiveDataProvider([
                        'query' => Task::find(),
                    ]);
                }
            );

            return $this->successResponse($data);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Task list');
        }
    }

    /**
     * View task
     * 
     * @param int $id
     * @return Task
     * @throws NotFoundHttpException
     */
    public function actionView(int $id)
    {
        try {
            $model = $this->findModel($id);
            return $this->successResponse($model);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Task view');
        }
    }

    /**
     * Create task
     * 
     * @return Task|array
     */
    public function actionCreate()
    {
        try {
            $model = new Task();
            $postData = Yii::$app->request->post();

            if (empty($postData)) {
                throw new InvalidArgumentException('Request body is empty');
            }

            $model->load($postData, '');

            $this->validateModelOrFail($model);
            $this->saveModelOrFail($model, 'create');

            $this->cacheService->clearTasksCache();
            return $this->successResponse($model, 'Task created successfully', 201);
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Task create');
        }
    }

    /**
     * Update task
     * 
     * @param int $id
     * @return Task|array
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id)
    {
        try {
            $model = $this->findModel($id);
            $postData = Yii::$app->request->post();

            if (empty($postData)) {
                throw new InvalidArgumentException('Request body is empty');
            }

            $model->load($postData, '');

            $this->validateModelOrFail($model);
            $this->saveModelOrFail($model, 'update');

            $this->cacheService->clearTasksCache();
            return $this->successResponse($model, 'Task updated successfully');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Task update');
        }
    }

    /**
     * Delete task
     * 
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id)
    {
        try {
            $model = $this->findModel($id);

            if ($model->delete() === false) {
                throw new SaveModelException($model, 'delete');
            }

            $this->cacheService->clearTasksCache();
            return $this->successResponse(null, 'Task deleted successfully');
        } catch (\Throwable $e) {
            return $this->handleException($e, 'Task delete');
        }
    }

    /**
     * Find model by ID
     * 
     * @param int $id
     * @return Task
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        $model = Task::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Task not found.');
        }

        return $model;
    }
}
