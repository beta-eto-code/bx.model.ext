<?php

namespace Bx\Model\Ext\Common;

use Bx\Model\Ext\Interfaces\RemoveStrategyInterface;
use Bx\Model\Ext\Interfaces\StorageInterface;
use Bx\Model\Interfaces\ModelInterface;

class ModelStorage implements StorageInterface
{
    /**
     * @var ModelInterface[]
     */
    private $list;
    /**
     * @var RemoveStrategyInterface
     */
    private $removeStrategy;
    /**
     * @var string
     */
    private $pkName;
    /**
     * @var int
     */
    private $limitCount;

    public function __construct(
        RemoveStrategyInterface $removeStrategy,
        string $pkName = 'ID',
        array $list = [],
        int $limitCount = 10000
    ) {
        $this->list = [];
        $this->removeStrategy = $removeStrategy;
        $this->pkName = $pkName;
        $this->limitCount = $limitCount;
        $this->init($list);
    }

    /**
     * @param ModelInterface[] $list
     * @return void
     */
    private function init(array $list)
    {
        $this->removeStrategy->flush();
        foreach ($list as $item) {
            if (!($item instanceof ModelInterface)) {
                continue;
            }

            $pkValue = $item[$this->pkName] ?? null;
            if (empty($pkValue)) {
                continue;
            }

            $this->list[$pkValue] = $item;
            $this->removeStrategy->touch($pkValue);
        }
    }

    /**
     * @param string $key
     * @param mixed|ModelInterface $item
     * @return void
     */
    public function add(string $key, $item)
    {
        if (count($this->list) >= $this->limitCount) {
            $this->removeItem();
        }

        $this->list[$key] = $item;
        $this->removeStrategy->touch($key);
    }

    /**
     * @return void
     */
    private function removeItem()
    {
        $key = $this->removeStrategy->getKeyForRemove();
        if ($this->has($key)) {
            unset($this->list[$key]);
            return;
        }

        $this->removeItem();
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->list[$key]);
    }

    /**
     * @param string $key
     * @return ModelInterface|null
     */
    public function get(string $key)
    {
        $result = $this->list[$key] ?? null;
        if ($result instanceof ModelInterface) {
            $this->removeStrategy->touch($key);
            return $result;
        }

        return null;
    }
}
