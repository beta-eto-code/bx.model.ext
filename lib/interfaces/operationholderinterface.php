<?php

namespace Bx\Model\Ext\Interfaces;

use Bx\Model\Interfaces\ModelInterface;
use Bx\Model\Interfaces\ModelServiceInterface;
use Iterator;

interface OperationHolderInterface extends CommitableInterface
{
    public function addOperationCreate(ModelInterface $model, ModelServiceInterface $service, string $pkName): ModelOperationInterface;

    public function addOperationUpdate(ModelInterface $model, ModelServiceInterface $service, string $pkName): ModelOperationInterface;

    public function addOperationRemove($pkValue, ModelServiceInterface $service): ModelOperationInterface;

    public function getCreateOperationList(?string $serviceName = null): Iterator;

    public function getUpdateOperationList(?string $serviceName = null): Iterator;

    public function getRemoveOperationList(?string $serviceName = null): Iterator;

    public function getOperationList(?string $serviceName): Iterator;

    public function actualizeOperations();

    public function flush();
}
