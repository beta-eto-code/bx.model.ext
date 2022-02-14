<?php

namespace Bx\Model\Ext\Common;

use Bx\Model\Collection;
use Bx\Model\Ext\DataHelper;
use Bx\Model\Ext\Interfaces\OperationHolderInterface;
use Bx\Model\Interfaces\ModelInterface;
use Bx\Model\Interfaces\ModelServiceInterface;
use Bx\Model\Ext\Interfaces\ModelOperationInterface;
use Bx\Model\Interfaces\CollectionInterface;
use EmptyIterator;
use Iterator;

class OperationHolder implements OperationHolderInterface
{
    /**
     * @var array
     */
    private $crateOperations;
    /**
     * @var array
     */
    private $updateOperations;
    /**
     * @var array
     */
    private $removeOperations;

    public function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->crateOperations = [];
        $this->updateOperations = [];
        $this->removeOperations = [];
    }

    public function addOperationCreate(ModelInterface $model, ModelServiceInterface $service, string $pkName): ModelOperationInterface
    {
        $serviceClass = get_class(DataHelper::extractOriginalObject($service));
        if (empty($this->crateOperations[$serviceClass])) {
            $this->crateOperations[$serviceClass] = new Collection();
        }

        $operation = ModelOperation::initCreateOperation($model, $service, $pkName);
        $this->crateOperations[$serviceClass]->append($operation);

        return $operation;
    }

    public function addOperationUpdate(ModelInterface $model, ModelServiceInterface $service, string $pkName): ModelOperationInterface
    {
        $serviceClass = get_class(DataHelper::extractOriginalObject($service));
        if (empty($this->updateOperations[$serviceClass])) {
            $this->updateOperations[$serviceClass] = new Collection();
        }

        $operation = ModelOperation::initUpdateOperation($model, $service, $pkName);
        $this->updateOperations[$serviceClass]->append($operation);

        return $operation;
    }

    public function addOperationRemove($pkValue, ModelServiceInterface $service): ModelOperationInterface
    {
        $serviceClass = get_class(DataHelper::extractOriginalObject($service));
        if (empty($this->removeOperations[$serviceClass])) {
            $this->removeOperations[$serviceClass] = new Collection();
        }

        $operation = ModelOperation::initRemoveOperation($pkValue, $service);
        $this->removeOperations[$serviceClass]->append($operation);

        return $operation;
    }

    /**
     * @param string|null $serviceName
     * @return Iterator|ModelOperationInterface[]
     */
    public function getCreateOperationList(?string $serviceName = null): Iterator
    {
        if (!empty($serviceName) && $this->crateOperations[$serviceName] instanceof Iterator) {
            return $this->crateOperations[$serviceName];
        }

        if (empty($serviceName)) {
            foreach($this->crateOperations as $serviceList) {
                foreach($serviceList as $operation) {
                    yield $operation;
                }
            }
        }

        return new EmptyIterator();
    }

    /**
     * @param string|null $serviceName
     * @return Iterator|ModelOperationInterface[]
     */
    public function getUpdateOperationList(?string $serviceName = null): Iterator
    {
        if (!empty($serviceName) && $this->updateOperations[$serviceName] instanceof Iterator) {
            return $this->updateOperations[$serviceName];
        }

        if (empty($serviceName)) {
            foreach($this->updateOperations as $serviceList) {
                foreach($serviceList as $operation) {
                    yield $operation;
                }
            }
        }

        return new EmptyIterator();
    }

    /**
     * @param string|null $serviceName
     * @return Iterator|ModelOperationInterface[]
     */
    public function getRemoveOperationList(?string $serviceName = null): Iterator
    {
        if (!empty($serviceName) && $this->removeOperations[$serviceName] instanceof Iterator) {
            return $this->removeOperations[$serviceName];
        }

        if (empty($serviceName)) {
            foreach($this->removeOperations as $serviceList) {
                foreach($serviceList as $operation) {
                    yield $operation;
                }
            }
        }

        return new EmptyIterator();
    }

    /**
     * @param ModelOperationInterface $operation
     * @return CollectionInterface
     */
    private function getCollectionByOperation(ModelOperationInterface $operation): CollectionInterface
    {
        $operationType = $operation->getOperationType();
        $serviceClass = get_class(DataHelper::extractOriginalObject($operation->getService()));
        if ($operationType === ModelOperationInterface::CREATE_OPERATION) {
            return $this->crateOperations[$serviceClass] instanceof CollectionInterface ? 
                $this->crateOperations[$serviceClass] : 
                new Collection();
        }

        if ($operationType === ModelOperationInterface::UPDATE_OPERATION) {
            return $this->updateOperations[$serviceClass] instanceof CollectionInterface ? 
                $this->updateOperations[$serviceClass] : 
                new Collection();
        }

        if ($operationType === ModelOperationInterface::REMOVE_OPERATION) {
            return $this->removeOperations[$serviceClass] instanceof CollectionInterface ? 
                $this->removeOperations[$serviceClass] : 
                new Collection();
        }
    
        return new Collection();
    }

    /**
     * @param string|null $serviceName
     * @return Iterator|ModelOperationInterface[]
     */
    public function getOperationList(?string $serviceName = null): Iterator
    {
        foreach($this->getCreateOperationList($serviceName) as $operation) {
            yield $operation;
        }

        foreach($this->getUpdateOperationList($serviceName) as $operation) {
            yield $operation;
        }

        foreach($this->getRemoveOperationList($serviceName) as $operation) {
            yield $operation;
        }

        return new EmptyIterator();
    }

    /**
     * @param string $serviceName
     * @param string $operationType
     * @return CollectionInterface
     */
    private function getCollection(string $serviceName, string $operationType): CollectionInterface
    {
        $emptyCollection = new Collection();
        switch($operationType) {
            case ModelOperationInterface::CREATE_OPERATION:
                return $this->crateOperations[$serviceName] ?? $emptyCollection;
            case ModelOperationInterface::UPDATE_OPERATION:
                return $this->updateOperations[$serviceName] ?? $emptyCollection;
            case ModelOperationInterface::REMOVE_OPERATION:
                return $this->removeOperations[$serviceName] ?? $emptyCollection;
        }

        return $emptyCollection;
    }

    private function actualizeCreateOperations()
    {
        foreach($this->crateOperations as $serviceName => $collection) {
            $itemsForRemove = [];
            /**
             * @var CollectionInterface|ModelOperationInterface[] $collection
             */
            foreach($collection as $operation) {
                if ($operation->isFinished()) {
                    $itemsForRemove[] = $operation;
                    continue;
                }

                $pkValue = $operation->getPkValue();
                if (!empty($pkValue)) {
                    $itemsForRemove[] = $operation;
                }
            }

            foreach($itemsForRemove as $operation) {
                $collection->remove($operation);
            }
        }
    }

    private function actualizeUpdateOperations()
    {
        foreach($this->updateOperations as $serviceName => $collection) {
            $ids = [];
            $removeCollection = $this->getCollection($serviceName, ModelOperationInterface::REMOVE_OPERATION);
            $idsRemoveOperations = $removeCollection->map(function(ModelOperationInterface $operation) {
                return $operation->getPkValue();
            });

            $itemsForRemove = [];
            /**
             * @var CollectionInterface|ModelOperationInterface[] $collection
             */
            foreach($collection as $operation) {
                if ($operation->isFinished()) {
                    $itemsForRemove[] = $operation;
                    continue;
                }

                $pkValue = $operation->getPkValue();
                if (empty($pkValue)) {
                    $itemsForRemove[] = $operation;
                    continue;
                }
                
                if (in_array($pkValue, $idsRemoveOperations) || in_array($pkValue, $ids)) {
                    $itemsForRemove[] = $operation;
                    continue;
                }
                $ids[] = $pkValue;
            }

            foreach($itemsForRemove as $operation) {
                $collection->remove($operation);
            }
        }
    }

    private function actualizeRemoveOperations()
    {
        foreach($this->removeOperations as $serviceName => $collection) {
            $ids = [];
            $itemsForRemove = [];
            /**
             * @var CollectionInterface|ModelOperationInterface[] $collection
             */
            foreach($collection as $operation) {
                if ($operation->isFinished()) {
                    $itemsForRemove[] = $operation;
                    continue;
                }

                if (in_array($operation->getPkValue(), $ids)) {
                    $itemsForRemove[] = $operation;
                    continue;
                }
                $ids[] = $operation->getPkValue();
            }

            foreach($itemsForRemove as $operation) {
                $collection->remove($operation);
            }
        }
    }

    public function actualizeOperations()
    {
        $this->actualizeCreateOperations();
        $this->actualizeUpdateOperations();
        $this->actualizeRemoveOperations();
    }

    /**
     * @param string|null $serviceName
     * @return void
     */
    private function clearFinishedOperations(?string $serviceName = null)
    {
        /**
         * @var ModelOperationInterface[] $operationForRemove
         */
        $operationForRemove = [];
        foreach ($this->getOperationList($serviceName) as $operation) {
            if ($operation->isFinished()) {
                $operationForRemove[] = $operation;
            }
        }

        foreach($operationForRemove as $operation) {
            $this->getCollectionByOperation($operation)->remove($operation);
        }
    }

    /**
     * @param string|null $serviceName
     * @return Iterator
     */
    public function commit()
    {
        $this->clearFinishedOperations();
        foreach ($this->getOperationList() as $operation) {
            yield $operation->commit();
        }

        $this->clearFinishedOperations();

        return new EmptyIterator();
    }

    public function flush()
    {
        $this->init();
    }
}
