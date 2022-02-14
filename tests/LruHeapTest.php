<?php

namespace Bx\Model\Ext\Tests;

use Bx\Model\Ext\Common\LruHeap;
use PHPUnit\Framework\TestCase;

class LruHeapTest extends TestCase
{
    /**
     * @var LruHeap
     */
    private $lruHeap;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->lruHeap = new LruHeap();
    }

    public function testResort()
    {
        $this->lruHeap->insert('one');
        $this->lruHeap->insert('two');

        $one = $this->lruHeap->getByKey('one');
        $this->assertEquals($one, $this->lruHeap->top());

        $one->index = 2;
        $this->assertEquals($one, $this->lruHeap->top());

        $two = $this->lruHeap->getByKey('two');
        $this->lruHeap->resort();
        $this->assertEquals($two, $this->lruHeap->top());
    }

    public function testExtract()
    {
        $this->lruHeap->insert('one');
        $this->lruHeap->insert('two');

        $this->assertEquals('one', $this->lruHeap->extract());
        $this->assertEquals('two', $this->lruHeap->extract());
    }

    public function testInsert()
    {
        $this->lruHeap->insert('one');
        $this->lruHeap->insert('two');

        $one = $this->lruHeap->getByKey('one');
        $two = $this->lruHeap->getByKey('two');

        $this->assertEquals('one', $one->data);
        $this->assertEquals(0, $one->index);

        $this->assertEquals('two', $two->data);
        $this->assertEquals(1, $two->index);

        $this->lruHeap->insert('one');
        $this->assertEquals('one', $one->data);
        $this->assertEquals(2, $one->index);
    }

    public function testGetByKey()
    {
        $this->lruHeap->insert('one');
        $one = $this->lruHeap->getByKey('one');
        $this->assertEquals('one', $one->data);
    }
}
