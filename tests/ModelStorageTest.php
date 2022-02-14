<?php

namespace Bx\Model\Ext\Tests;

use Bx\Model\Ext\Common\LruRemoveStrategy;
use Bx\Model\Ext\Common\ModelStorage;
use Bx\Model\Ext\Tests\Samples\SimpleModel;
use Exception;
use PHPUnit\Framework\TestCase;

class ModelStorageTest extends TestCase
{
    /**
     * @var ModelStorage
     */
    private $storage;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $removeStrategy = new LruRemoveStrategy();
        $this->storage = new ModelStorage(
            $removeStrategy,
            'ID',
            [],
            2
        );
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testGet()
    {
        $model = new SimpleModel(['ID' => 1, 'NAME' => 'one']);
        $this->storage->add(1, $model);
        $this->assertEquals($model, $this->storage->get(1));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testHas()
    {
        $this->assertFalse($this->storage->has(1));
        $model = new SimpleModel(['ID' => 1, 'NAME' => 'one']);
        $this->storage->add(1, $model);
        $this->assertTrue($this->storage->has(1));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testAdd()
    {
        $this->assertFalse($this->storage->has(1));
        $model = new SimpleModel(['ID' => 1, 'NAME' => 'one']);
        $this->storage->add(1, $model);
        $this->assertTrue($this->storage->has(1));
        $this->assertEquals($model, $this->storage->get(1));
    }
}
