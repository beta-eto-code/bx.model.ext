<?php

namespace Bx\Model\Ext;

use Bx\Model\Ext\Interfaces\DecoratorInterface;
use Bx\Model\Ext\Interfaces\LazyModelInterface;
use Bx\Model\Ext\Interfaces\LoadableInterface;
use Bx\Model\Interfaces\CollectionItemInterface;
use Bx\Model\Interfaces\ModelCollectionInterface;
use Bx\Model\Interfaces\ModelInterface;
use Bx\Model\Interfaces\Models\ReadableModelServiceInterface;
use Bx\Model\Interfaces\ReadableCollectionInterface;
use Bx\Model\ModelCollection;
use SplObjectStorage;
use Traversable;

class LazyModelCollection extends ModelCollection implements LoadableInterface, DecoratorInterface
{
    private $service;
    private $pkName;
    /**
     * @var false
     */
    private $loaded;

    /**
     * @param $list
     * @param string $className
     * @param ReadableModelServiceInterface $service
     * @param string $pkName
     */
    public function __construct($list, string $className, ReadableModelServiceInterface $service, string $pkName = 'ID')
    {
        $this->service = $service;
        $this->pkName = $pkName;
        $this->loaded = false;
        $this->items = new SplObjectStorage();

        $this->className = $className;
        foreach ($list as $item) {
            if ($item instanceof $className || $item instanceof LazyModelInterface) {
                $this->items->attach($item);
            } elseif(is_array($item) || $item instanceof Traversable) {
                $this->items->attach(new $className($item));
            }
        }
    }

    /**
     * @return ModelCollectionInterface
     */
    public function getOriginalObject(): object
    {
        $newCollection = new ModelCollection([], $this->className);
        foreach ($this as $item) {
            $originalItem = DataHelper::extractOriginalObject($item);
            if ($originalItem instanceof ModelInterface) {
                $newCollection->append($originalItem);
            }
        }

        return $newCollection;
    }

    /**
     * @param $list
     * @return ReadableCollectionInterface
     */
    protected function newCollection($list): ReadableCollectionInterface
    {
        return new static($list, $this->className, $this->service, $this->pkName);
    }

    public function append(CollectionItemInterface $item)
    {
        if ($item instanceof LoadableInterface && !$item->isLoaded()) {
            $this->loaded = false;
        }

        if ($item instanceof $this->className || $item instanceof LoadableInterface) {
            $this->items->attach($item);
        }
    }

    public function load(): bool
    {
        if ($this->loaded) {
            return false;
        }

        $pkValueList = $this->filter(function (ModelInterface $model) {
            return ($model instanceof LoadableInterface) && !$model->isLoaded();
        })->column($this->pkName);

        if (empty($pkValueList)) {
            $this->loaded = true;
            return false;
        }

        $collection = $this->service->getList([
            'filter' => [
                "=$this->pkName" => $pkValueList,
            ]
        ]);

        if ($collection->count() === 0) {
            $this->loaded = true;
            return false;
        }

        foreach ($this as $model) {
            if (!($model instanceof LazyModelInterface) || $model->isLoaded()) {
                continue;
            }

            $newModel = $collection->findByKey($this->pkName, $model->getPkValue());
            if (empty($newModel) || !($newModel instanceof ModelInterface)) {
                continue;
            }

            $model->loadFrom($newModel);
        }

        $this->loaded = true;
        return true;
    }

    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    public function getApiModel(): array
    {
        $result = [];
        foreach($this as $item) {
            $result[] = $item->jsonSerialize();
        }

        return $result;
    }
}
