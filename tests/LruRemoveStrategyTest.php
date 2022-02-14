<?php

namespace Bx\Model\Ext\Tests;

use Bx\Model\Ext\Common\LruRemoveStrategy;
use PHPUnit\Framework\TestCase;

class LruRemoveStrategyTest extends TestCase
{
    /**
     * @var LruRemoveStrategy
     */
    private $removeStrategy;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->removeStrategy = new LruRemoveStrategy();
    }

    public function testFlush()
    {
        $this->removeStrategy->touch('one');
        $this->removeStrategy->touch('two');
        $this->removeStrategy->flush();
        $this->assertEquals('', $this->removeStrategy->getKeyForRemove());
    }

    public function testGetKeyForRemove()
    {
        $this->removeStrategy->touch('one');
        $this->removeStrategy->touch('two');
        $this->assertEquals('one', $this->removeStrategy->getKeyForRemove());
        $this->assertEquals('two', $this->removeStrategy->getKeyForRemove());

        $this->removeStrategy->touch('one');
        $this->removeStrategy->touch('two');
        $this->removeStrategy->touch('one');
        $this->assertEquals('two', $this->removeStrategy->getKeyForRemove());
        $this->assertEquals('one', $this->removeStrategy->getKeyForRemove());
    }

    public function testTouch()
    {
        $this->assertEquals('', $this->removeStrategy->getKeyForRemove());
        $this->removeStrategy->touch('two');
        $this->assertEquals('two', $this->removeStrategy->getKeyForRemove());
    }
}
