<?php

declare(strict_types=1);

namespace app\traits;

use Yii;
use yii\base\Model;
use yii\base\ModelException;
use yii\base\InvalidArgumentException;
use yii\base\InvalidConfigException;
use yii\db\Exception as DBException;
use yii\web\HttpException;
use app\exceptions\ValidationException;
use app\exceptions\SaveModelException;

trait ApiResponseTrait
{
    /**
     * Generates a successful response
     * 
     * @param mixed $data
     * @param string|null $message
     * @param int $code
     * @return array
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $code = 200): array
    {
        Yii::$app->response->statusCode = $code;

        $response = [
            'success' => true,
            'data' => $data,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return $response;
    }

    /**
     * Generates a response with an error
     * 
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return array
     */
    protected function errorResponse(string $message, int $code = 400, mixed $errors = null): array
    {
        Yii::$app->response->statusCode = $code;

        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $response;
    }

    /**
     * Handling exceptions with proper classification
     * 
     * @param Throwable $e
     * @param string $context
     * @return array
     */
    protected function handleException(\Throwable $e, string $context = ''): array
    {
        $logContext = $context ? "{$context}: " : '';

        if ($e instanceof ValidationException) {
            Yii::warning($logContext . $e->getFirstErrorsAsString());

            $formattedErrors = [];
            foreach ($e->getModelErrors() as $attribute => $attributeErrors) {
                $formattedErrors[$attribute] = $attributeErrors[0];
            }

            return $this->errorResponse(
                'Validation failed',
                422,
                $formattedErrors
            );
        } elseif ($e instanceof SaveModelException) {
            $model = $e->getModel();
            $logDetails = [
                'operation' => $e->getOperation(),
                'model' => get_class($model),
                'attributes' => $model->attributes,
                'errors' => $model->getErrors(),
            ];

            Yii::error($logContext . $e->getMessage(), $logDetails);

            return $this->errorResponse(
                'Failed to save data',
                422,
                $model->getErrors()
            );
        } elseif ($e instanceof HttpException) {
            Yii::warning($logContext . $e->getMessage());

            return $this->errorResponse(
                $this->getHttpErrorMessage($e->statusCode),
                $e->statusCode
            );
        } elseif ($e instanceof DBException) {
            Yii::error($logContext . $e->getMessage() . "\n" . $e->getTraceAsString());

            $userMessage = YII_DEBUG ? $e->getMessage() : 'Database error occurred';
            return $this->errorResponse($userMessage, 503);
        } else {
            $logLevel = $e instanceof UserException ? 'warning' : 'error';

            if ($logLevel === 'error') {
                Yii::error($logContext . $e->getMessage() . "\n" . $e->getTraceAsString());
            } else {
                Yii::warning($logContext . $e->getMessage());
            }

            $code = $e instanceof UserException ? 400 : 500;
            $userMessage = YII_DEBUG ? $e->getMessage() : 'An error occurred';

            return $this->errorResponse($userMessage, $code);
        }
    }

    /**
     * Helper for saving a model with exception
     */
    protected function saveModelOrFail(Model $model, string $operation = 'save'): bool
    {
        if (!$model->save()) {
            throw new SaveModelException($model, $operation);
        }
        return true;
    }

    /**
     * Helper for validation with exception
     */
    protected function validateModelOrFail(Model $model): bool
    {
        if (!$model->validate()) {
            throw new ValidationException($model);
        }
        return true;
    }

    /**
     * Standard HTTP error messages
     * 
     * @param int $code
     * @return string
     */
    protected function getHttpErrorMessage(int $code): string
    {
        $messages = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            422 => 'Unprocessable Entity',
            429 => 'Too Many Requests',
            503 => 'Service Unavailable',
        ];

        return $messages[$code] ?? 'An error occurred';
    }
}
