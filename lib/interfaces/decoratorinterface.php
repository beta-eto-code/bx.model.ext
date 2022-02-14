<?php

namespace Bx\Model\Ext\Interfaces;

interface DecoratorInterface
{
    public function getOriginalObject(): object;
}