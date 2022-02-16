<?php

namespace Bx\Model\Ext\Interfaces;

interface RemoveStrategyInterface
{
    /**
     * @param string $key
     * @return void
     */
    public function touch(string $key);

    /**
     * @return string
     */
    public function getKeyForRemove(): string;

    /**
     * @return void
     */
    public function flush();
}
