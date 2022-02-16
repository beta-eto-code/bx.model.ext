<?php

namespace Bx\Model\Ext\Common;

use Bitrix\Main\Result;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\Ext\Interfaces\ModelOperationInterface;
use Bx\Model\Interfaces\ModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;

final class ModelOperation implements ModelOperationInterface
{
    /**
     * @var ModelServiceInterface
     */
    private $modelService;
    /**
     * @var string
     */
    private $operationType;
    /**
     * @var AbsOptimizedModel
     */
    private $model;
    /**
     * @var mixed
     */
    private $pkValue;
    /**
     * @var Result|null
     */
    private $result;
    /**
     * @var string
     */
    private $pkName;
    /**
     * @var UserContextInterface|null
     */
    private $userContext;

    private function __construct(
        ModelServiceInterface $service,
        string $operationType,
        ?AbsOptimizedModel $model = null,
        string $pkName = 'ID',
        $pkValue = null
    ) {
        $this->modelService = $service;
        $this->operationType = $operationType;
        $this->model = $model;
        $this->pkValue = $pkValue;
        $this->result = null;
        $this->pkName = $pkName;
    }

    /**
     * @param AbsOptimizedModel $model
     * @param ModelServiceInterface $service
     * @return ModelOperationInterface
     */
    public static function initCreateOperation(
        AbsOptimizedModel $model,
        ModelServiceInterface $service,
        string $pkName
    ): ModelOperationInterface {
        return new static($service, ModelOperationInterface::CREATE_OPERATION, $model, $pkName);
    }

    /**
     * @param AbsOptimizedModel $model
     * @param ModelServiceInterface $service
     * @return ModelOperationInterface
     */
    public static function initUpdateOperation(
        AbsOptimizedModel $model,
        ModelServiceInterface $service,
        string $pkName
    ): ModelOperationInterface {
        return new static($service, ModelOperationInterface::UPDATE_OPERATION, $model, $pkName);
    }

    /**
     * @param mixed $pkValue
     * @param ModelServiceInterface $service
     * @return ModelOperationInterface
     */
    public static function initRemoveOperation(
        $pkValue,
        ModelServiceInterface $service
    ): ModelOperationInterface {
        return new static(
            $service,
            ModelOperationInterface::REMOVE_OPERATION,
            null,
            '',
            $pkValue
        );
    }

    public function setUserContext(?UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
    }

    /**
     * @return string
     */
    public function getOperationType(): string
    {
        return $this->operationType;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->result instanceof Result;
    }

    /**
     * @return mixed
     */
    public function getPkValue()
    {
        if ($this->operationType === ModelOperationInterface::REMOVE_OPERATION) {
            return $this->pkValue;
        }

        return $this->model[$this->pkName] ?? null;
    }

    /**
     * @return AbsOptimizedModel|null
     */
    public function getModel(): ?AbsOptimizedModel
    {
        return $this->model;
    }

    /**
     * @return ModelServiceInterface
     */
    public function getService(): ModelServiceInterface
    {
        return $this->modelService;
    }

    /**
     * @return Result
     */
    public function commit()
    {
        if ($this->result instanceof Result) {
            return $this->result;
        }

        if (
            in_array($this->operationType, [
                ModelOperationInterface::CREATE_OPERATION,
                ModelOperationInterface::UPDATE_OPERATION
            ])
        ) {
            return $this->result = $this->modelService->save($this->model, $this->userContext);
        }

        return $this->result = $this->modelService->delete($this->pkValue, $this->userContext);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function assertValueByKey(string $key, $value): bool
    {
        if ($this->model instanceof AbsOptimizedModel) {
            return $this->model->assertValueByKey($key, $value);
        }

        return false;
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function hasValueKey(string $key): bool
    {
        if ($this->model instanceof AbsOptimizedModel) {
            return $this->model->hasValueKey($key);
        }

        return false;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getValueByKey(string $key)
    {
        if ($this->model instanceof AbsOptimizedModel) {
            return $this->model->getValueByKey($key);
        }

        return null;
    }

    public function jsonSerialize(): array
    {
        return [
            'operation' => $this->operationType,
            'pk' => $this->pkValue,
            'model' => $this->model instanceof AbsOptimizedModel ? $this->model->getApiModel() : null,
        ];
    }
}
