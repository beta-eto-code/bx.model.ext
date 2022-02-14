<?php

namespace Bx\Model\Ext;

use Bitrix\Main\Result;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\Ext\Common\TransactionResult;
use Bx\Model\Ext\Interfaces\OperationHolderInterface;
use Bx\Model\Interfaces\Models\ReadableModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\ModelCollection;

class TransactionModelService extends ServiceDecorator
{
    /**
     * @var OperationHolderInterface
     */
    private $operationHolder;
    /**
     * @var string
     */
    private $modelClass;
    /**
     * @var string
     */
    private $pkName;

    public function __construct(
        OperationHolderInterface $operationHolder, 
        ReadableModelServiceInterface $service, 
        string $modelClass,
        string $pkName = 'ID'
    ) {
        parent::__construct($service);
        $this->operationHolder = $operationHolder;
        $this->modelClass = $modelClass;
        $this->pkName = $pkName;
    }

    public function getList(array $params, UserContextInterface $userContext = null): ModelCollection
    {
        $newCollection = new ModelCollection([], $this->modelClass);
        $collection = $this->modelService->getList($params, $userContext);
        foreach($collection as $item) {
            if (!($item instanceof StateModel)) {
                $item = new StateModel($item);
            }

            $newCollection->append($item);
        }

        return $newCollection;
    }

    public function getById(int $id, UserContextInterface $userContext = null): ?AbsOptimizedModel
    {
        $model = $this->modelService->getById($id, $userContext);
        if ($model instanceof AbsOptimizedModel && !($model instanceof StateModel)) {
            $model = new StateModel($model);
        }

        return $model;
    }

    /**
     * @param integer $id
     * @param UserContextInterface|null $userContext
     * @return Result|TransactionResult
     */
    public function delete(int $id, UserContextInterface $userContext = null): Result
    {
        if (!empty($id)) {
            $operation = $this->operationHolder->addOperationRemove($id, $this->modelService);
            return new TransactionResult($operation);
        }

        return new Result();
    }

    /**
     * @param AbsOptimizedModel|StateModel $model
     * @param UserContextInterface|null $userContext
     * @return Result|TransactionResult
     */
    public function save(AbsOptimizedModel $model, UserContextInterface $userContext = null): Result
    {
        if (empty($model[$this->pkName])) {
            $operation = $this->operationHolder->addOperationCreate($model, $this->modelService, $this->pkName);
            $operation->setUserContext($userContext);
            return new TransactionResult($operation);
        } else if($model->isChanged()) {
            $operation = $this->operationHolder->addOperationUpdate($model, $this->modelService, $this->pkName);
            $operation->setUserContext($userContext);
            return new TransactionResult($operation);
        }

        return new Result();
    }
}
