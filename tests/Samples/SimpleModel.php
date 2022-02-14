<?php

namespace Bx\Model\Ext\Tests\Samples;

use Bx\Model\AbsOptimizedModel;

class SimpleModel extends AbsOptimizedModel
{
    /**
     * @return array
     */
    protected function toArray(): array
    {
        return $this->data;
    }
}
