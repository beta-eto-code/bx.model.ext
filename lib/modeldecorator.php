<?php

namespace Bx\Model\Ext;

use ArrayIterator;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\Ext\Interfaces\DecoratorInterface;
use Bx\Model\Interfaces\ModelInterface;
use Iterator;

abstract class ModelDecorator extends AbsOptimizedModel implements DecoratorInterface
{
    /**
     * @var AbsOptimizedModel
     */
    protected $model;

    /**
     * @param AbsOptimizedModel $model
     */
    public function __construct(AbsOptimizedModel $model) {
        $this->data = [];
        $this->model = $model;
    }

    /**
     * @return ModelInterface
     */
    public function getOriginalObject(): object
    {
        return $this->model;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function assertValueByKey(string $key, $value): bool
    {
        return $this->model->assertValueByKey($key, $value);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function hasValueKey(string $key): bool
    {
        return $this->model->hasValueKey($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getValueByKey(string $key)
    {
        return $this->model->getValueByKey($key);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->model->jsonSerialize();
    }

    /**
     * @deprecated
     * @param string $key
     * @return boolean
     */
    public function isFill(string $key): bool
    {
        return $this->model->isFill($key);
    }

    /**
     * @return array
     */
    public function getApiModel(): array
    {
        return $this->model->getApiModel();
    }

    /**
     * @param mixed $offset
     * @return bool
     * @throws ArgumentException
     * @throws SystemException
     */
    public function offsetExists($offset): bool
    {
        return $this->model->offsetExists($offset);
    }

    /**
     * @param mixed $offset
     * @return mixed
     * @throws ArgumentException
     * @throws SystemException
     */
    public function offsetGet($offset)
    {
        return $this->model->offsetGet($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @throws ArgumentException
     * @throws SystemException
     */
    public function offsetSet($offset, $value)
    {
        $this->model->offsetSet($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $this->model->offsetUnset($offset);
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        return $this->model->getData();
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): Iterator
    {
        return $this->model->getIterator();
    }

    protected function toArray(): array
    {
        return $this->model->toArray();
    }

    public function __call(string $method, array $args)
    {
        if (method_exists($this->model, $method)) {
            return $this->model->{$method}(...$args);
        }

        if (method_exists($this->model, '__call')) {
            return $this->model->__call($method, $args);
        }
    }
}
