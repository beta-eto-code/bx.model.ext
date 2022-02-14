<?php

namespace Bx\Model\Ext;

use Bx\Model\Ext\Interfaces\CommitableInterface;
use Bx\Model\Ext\Interfaces\DecoratorInterface;
use Bx\Model\Ext\Interfaces\LoadableInterface;
use Bx\Model\Interfaces\MappableInterface;

class DataHelper
{
    /**
     * @param object $obj
     * @return bool
     */
    public static function load(object $obj): bool
    {
        if ($obj instanceof LoadableInterface) {
            return $obj->load();
        }

        return false;
    }

    /**
     * @param object $obj
     * @param callable $fnMap - function($data): array
     * @return array
     */
    public static function map(object $obj, callable $fnMap): array
    {
        if ($obj instanceof MappableInterface) {
            return $obj->map($fnMap);
        }

        return [];
    }

    /**
     * @param object $obj
     * @return object
     */
    public static function extractOriginalObject(object $obj): object
    {
        while ($obj instanceof DecoratorInterface) {
            $obj = $obj->getOriginalObject();
        }

        return $obj;
    }

    /**
     * @param object $obj
     * @return mixed
     */
    public static function commit(object $obj)
    {
        if ($obj instanceof CommitableInterface) {
            return $obj->commit();
        }
    }
}
