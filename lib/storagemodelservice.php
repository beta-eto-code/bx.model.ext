<?php

namespace Bx\Model\Ext;

use Bx\Model\AbsOptimizedModel;
use Bx\Model\Ext\Common\LruRemoveStrategy;
use Bx\Model\Ext\Common\ModelStorage;
use Bx\Model\Ext\Interfaces\StorageInterface;
use Bx\Model\Interfaces\ModelInterface;
use Bx\Model\Interfaces\Models\ReadableModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\ModelCollection;

class StorageModelService extends ServiceDecorator
{
    /**
     * @var string
     */
    private $modelClassName;
    /**
     * @var string
     */
    private $pkName;
    /**
     * @var ModelStorage
     */
    private $storage;

    public function __construct(
        ReadableModelServiceInterface $service,
        string $modelClassName,
        string $pkName = 'ID',
        StorageInterface $storage = null
    ) {
        parent::__construct($service);
        $this->modelClassName = $modelClassName;
        $this->pkName = $pkName;
        $this->storage = $storage ?? new ModelStorage(new LruRemoveStrategy(), $this->pkName);
    }

    public function getList(array $params, UserContextInterface $userContext = null): ModelCollection
    {
        $newCollection = new ModelCollection([], $this->modelClassName);
        $collection = $this->modelService->getList($params, $userContext);
        foreach ($collection as $model) {
            $pkValue = $model[$this->pkName] ?? null;
            if (empty($pkValue)) {
                $newCollection->append($model);
                continue;
            }

            $storageItem = $this->storage->get((string)$pkValue);
            if ($storageItem instanceof ModelInterface) {
                $newCollection->append($storageItem);
                continue;
            }

            if ($model instanceof ModelInterface) {
                $this->storage->add($pkValue, $model);
                $newCollection->append($model);
            }
        }

        return $newCollection;
    }

    public function getCount(array $params, UserContextInterface $userContext = null): int
    {
        return $this->modelService->getCount($params, $userContext);
    }

    /**
     * @param int $id
     * @param UserContextInterface|null $userContext
     * @return AbsOptimizedModel|null
     */
    public function getById(int $id, UserContextInterface $userContext = null): ?AbsOptimizedModel
    {
        $storageItem = $this->storage->get((string)$id);
        if ($storageItem instanceof AbsOptimizedModel) {
            return $storageItem;
        }

        $model = $this->modelService->getById($id, $userContext);
        if ($model instanceof AbsOptimizedModel) {
            $this->storage->add($id, $model);
        }

        return $model;
    }
}
