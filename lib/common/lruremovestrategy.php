<?php

namespace Bx\Model\Ext\Common;

use Bx\Model\Ext\Interfaces\RemoveStrategyInterface;

class LruRemoveStrategy implements RemoveStrategyInterface
{
    /**
     * @var LruHeap
     */
    private $heap;

    public function __construct()
    {
        $this->heap = new LruHeap();
    }

    /**
     * @param string $key
     * @return void
     */
    public function touch(string $key)
    {
        $this->heap->insert($key);
    }

    /**
     * @return string
     */
    public function getKeyForRemove(): string
    {
        if (!$this->heap->valid()) {
            return '';
        }

        return $this->heap->extract();
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->heap = new LruHeap();
    }
}
