<?php

namespace Bx\Model\Ext\Common;

use Bitrix\Main\Result;
use Bx\Model\Ext\Interfaces\ModelOperationInterface;
use Bx\Model\Ext\Interfaces\CommitableInterface;

class TransactionResult extends Result implements CommitableInterface
{
    /**
     * @var ModelOperationInterface
     */
    private $operation;

    public function __construct(ModelOperationInterface $operation)
    {
        $this->operation = $operation;
    }

    /**
     * @return ModelOperationInterface
     */
    public function getOperation(): ModelOperationInterface
    {
        return $this->operation;
    }

    /**
     * @return boolean
     */
    public function isSuccess(): bool
    {
        return $this->operation->isFinished() ? $this->operation->commit()->isSuccess() : true;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->operation->isFinished() ? $this->operation->commit()->getErrors() : [];
    }

    /**
     * @return array
     */
    public function getErrorMessages(): array
    {
        return $this->operation->isFinished() ? $this->operation->commit()->getErrorMessages() : [];
    }

    /**
     * @return void
     */
    public function commit()
    {
        $this->operation->commit();
    }
}
