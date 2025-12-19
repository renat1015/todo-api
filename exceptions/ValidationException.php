<?php

declare(strict_types=1);

namespace app\exceptions;

use yii\base\Model;
use yii\base\UserException;

/**
 * Exception for validation errors
 */
class ValidationException extends UserException
{
    private Model $_model;
    private array $_errors;

    public function __construct(
        Model $model,
        string $message = 'Validation failed',
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->_model = $model;
        $this->_errors = $model->getErrors();
        parent::__construct($message, $code, $previous);
    }

    public function getModel(): Model
    {
        return $this->_model;
    }

    public function getModelErrors(): array
    {
        return $this->_errors;
    }

    public function getFirstErrorsAsString(): string
    {
        $errors = $this->_model->getFirstErrors();
        return implode(', ', $errors);
    }
}
