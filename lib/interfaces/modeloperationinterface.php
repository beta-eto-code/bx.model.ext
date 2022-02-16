<?php

namespace Bx\Model\Ext\Interfaces;

use Bx\Model\AbsOptimizedModel;
use Bx\Model\Interfaces\CollectionItemInterface;
use Bx\Model\Interfaces\ModelInterface;
use Bx\Model\Interfaces\ModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;

interface ModelOperationInterface extends CollectionItemInterface, CommitableInterface
{
    public const CREATE_OPERATION = 'create';
    public const UPDATE_OPERATION = 'update';
    public const REMOVE_OPERATION = 'remove';

    /**
     * @return string
     */
    public function getOperationType(): string;

    /**
     * @param UserContextInterface|null $userContext
     * @return void
     */
    public function setUserContext(?UserContextInterface $userContext);

    /**
     * @return bool
     */
    public function isFinished(): bool;

    /**
     * @return mixed
     */
    public function getPkValue();

    /**
     * @return ModelInterface|null
     */
    public function getModel(): ?ModelInterface;

    /**
     * @return ModelServiceInterface
     */
    public function getService(): ModelServiceInterface;
}
