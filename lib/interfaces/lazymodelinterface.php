<?php

namespace Bx\Model\Ext\Interfaces;

use Bx\Model\Interfaces\CollectionItemInterface;
use Bx\Model\Interfaces\ModelInterface;

interface LazyModelInterface extends LoadableInterface, CollectionItemInterface
{
    /**
     * @return mixed
     */
    public function getPkValue();

    /**
     * @param ModelInterface $model
     * @return mixed
     */
    public function loadFrom(ModelInterface $model);
}
