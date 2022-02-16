<?php

namespace Bx\Model\Ext\Common;

use Bx\Model\Interfaces\ModelInterface;

class LruItem
{
    /**
     * @var int
     */
    public $index;

    /**
     * @var mixed
     */
    public $data;

    /**
     * @param int $index
     * @param mixed $data
     */
    public function __construct(int $index, $data)
    {
        $this->index = $index;
        $this->data = $data;
    }
}
