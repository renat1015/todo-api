<?php

declare(strict_types=1);

namespace app\exceptions;

use yii\base\Model;
use yii\base\UserException;

/**
 * Exception for model saving errors
 */
class SaveModelException extends UserException
{
    private Model $_model;
    private array $_operation;

    public function __construct(
        Model $model,
        string $operation = 'save',
        ?string $message = null,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->_model = $model;
        $this->_operation = $operation;

        $defaultMessage = "Failed to {$operation} model";
        if ($message === null) {
            $errors = $model->getErrors();
            if (!empty($errors)) {
                $message = $defaultMessage . ': ' . $this->errorsToString($errors);
            } else {
                $message = $defaultMessage;
            }
        }

        parent::__construct($message, $code, $previous);
    }

    public function getModel(): Model
    {
        return $this->_model;
    }

    public function getOperation(): string
    {
        return $this->_operation;
    }

    private function errorsToString(array $errors): string
    {
        $messages = [];
        foreach ($errors as $attribute => $attributeErrors) {
            $messages[] = $attribute . ': ' . implode(', ', $attributeErrors);
        }
        return implode('; ', $messages);
    }
}
