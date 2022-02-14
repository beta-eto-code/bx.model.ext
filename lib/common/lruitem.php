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

    public function __construct(int $index, $data)
    {
        $this->index = $index;
        $this->data = $data;
    }
}
