<?php

namespace Bx\Model\Ext\Common;

use SplMinHeap;

class LruHeap extends SplMinHeap
{
    /**
     * @var array
     */
    private $map;
    /**
     * @var int
     */
    private $index;

    public function __construct()
    {
        $this->index = 0;
        $this->map = [];
    }
    /**
     * @param LruItem $value1
     * @param LruItem $value2
     * @return int
     */
    protected function compare($value1, $value2): int
    {
        return $value2->index - $value1->index;
    }

    /**
     * @param string $value
     * @return void
     */
    public function insert($value)
    {
        if (!is_string($value) || empty($value)) {
            return;
        }

        if (($this->map[$value] ?? null) instanceof LruItem) {
            $this->map[$value]->index = $this->index++;
            $this->resort();
            return;
        }

        $this->map[$value] = new LruItem($this->index++, $value);
        parent::insert($this->map[$value]);
    }

    /**
     * @return string
     */
    public function extract()
    {
        $lruItem = parent::extract();
        if (!($lruItem instanceof LruItem)) {
            return '';
        }

        unset($this->map[$lruItem->data]);
        return (string)$lruItem->data;
    }

    /**
     * @return void
     */
    public function resort()
    {
        if ($this->valid()) {
            $this->insert($this->extract());
        }
    }

    /**
     * @param string $key
     * @return LruItem|null
     */
    public function getByKey(string $key): ?LruItem
    {
        if ($this->map[$key] instanceof LruItem) {
            return $this->map[$key];
        }

        return null;
    }
}
