<?php

namespace Bx\Model\Ext\Interfaces;

use Bx\Model\Interfaces\ModelInterface;
use Bx\Model\Interfaces\ModelServiceInterface;
use Iterator;

interface OperationHolderInterface extends CommitableInterface
{
    /**
     * @param ModelInterface $model
     * @param ModelServiceInterface $service
     * @param string $pkName
     * @return ModelOperationInterface
     */
    public function addOperationCreate(
        ModelInterface $model,
        ModelServiceInterface $service,
        string $pkName
    ): ModelOperationInterface;

    /**
     * @param ModelInterface $model
     * @param ModelServiceInterface $service
     * @param string $pkName
     * @return ModelOperationInterface
     */
    public function addOperationUpdate(
        ModelInterface $model,
        ModelServiceInterface $service,
        string $pkName
    ): ModelOperationInterface;

    /**
     * @param mixed $pkValue
     * @param ModelServiceInterface $service
     * @return ModelOperationInterface
     */
    public function addOperationRemove($pkValue, ModelServiceInterface $service): ModelOperationInterface;

    /**
     * @param string|null $serviceName
     * @return Iterator
     */
    public function getCreateOperationList(?string $serviceName = null): Iterator;

    /**
     * @param string|null $serviceName
     * @return Iterator
     */
    public function getUpdateOperationList(?string $serviceName = null): Iterator;

    /**
     * @param string|null $serviceName
     * @return Iterator
     */
    public function getRemoveOperationList(?string $serviceName = null): Iterator;

    /**
     * @param string|null $serviceName
     * @return Iterator
     */
    public function getOperationList(?string $serviceName): Iterator;

    /**
     * @return void
     */
    public function actualizeOperations();

    /**
     * @return void
     */
    public function flush();
}
