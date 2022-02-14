<?php

namespace Bx\Model\Ext\Interfaces;

interface LoadableInterface
{
    /**
     * @return bool
     */
    public function load(): bool;

    /**
     * @return bool
     */
    public function isLoaded(): bool;
}
