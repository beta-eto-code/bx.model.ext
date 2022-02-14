<?php

namespace Bx\Model\Ext\Interfaces;

interface StorageInterface
{
    /**
     * @param string $key
     * @param $item
     * @return void
     */
    public function add(string $key, $item);

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);
}