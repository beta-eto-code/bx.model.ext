<?php

namespace Bx\Model\Ext;

use Bx\Model\AbsOptimizedModel;
use Bx\Model\Ext\Interfaces\LazyModelInterface;
use Bx\Model\Interfaces\ModelInterface;
use Bx\Model\Interfaces\Models\ReadableModelServiceInterface;
use Exception;

class LazyModel extends ModelDecorator implements LazyModelInterface
{
    /**
     * @var ReadableModelServiceInterface
     */
    private $service;
    /**
     * @var bool
     */
    private $loaded;
    /**
     * @var mixed
     */
    private $pkValue;

    /**
     * @param mixed $pkValue
     * @param AbsOptimizedModel $model
     * @param ReadableModelServiceInterface $service
     * @param bool $isLoaded
     * @throws Exception
     */
    public function __construct(
        $pkValue,
        AbsOptimizedModel $model,
        ReadableModelServiceInterface $service,
        bool $isLoaded = false
    ) {
        $this->data = [];
        $this->pkValue = $pkValue;
        $this->service = $service;
        $this->loaded = $isLoaded;
        parent::__construct($model);
    }

    public function load(): bool
    {
        if ($this->loaded) {
            return false;
        }

        $model = $this->service->getById($this->pkValue);
        if (empty($model)) {
            return false;
        }

        $this->loadFrom($model);
        return true;
    }

    /**
     * @param ModelInterface $model
     * @return void
     */
    public function loadFrom(ModelInterface $model)
    {
        foreach ($model as $key => $value) {
            $this->model[$key] = $value;
        }

        $this->loaded = true;
    }

    /**
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * @return mixed
     */
    public function getPkValue()
    {
        return $this->pkValue;
    }
}
