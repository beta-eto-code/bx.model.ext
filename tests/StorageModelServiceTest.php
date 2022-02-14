<?php

namespace Bx\Model\Tests;

use Bx\Model\Ext\StorageModelService;
use Bx\Model\Ext\Tests\Samples\SimpleModel;
use Bx\Model\Ext\Tests\Samples\SimpleModelService;
use Data\Provider\Providers\ArrayDataProvider;
use PHPUnit\Framework\TestCase;

class StorageModelServiceTest extends TestCase
{
    /**
     * @var SimpleModelService
     */
    private $service;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $dataProvider = new ArrayDataProvider([
            ['ID' => 1, 'NAME' => 'first'],
            ['ID' => 2, 'NAME' => 'second'],
        ]);
        $this->service = new StorageModelService(
            new SimpleModelService($dataProvider),
            SimpleModel::class
        );
    }

    public function testGetById()
    {
        $this->assertTrue($this->service->getById(1) === $this->service->getById(1));
    }

    public function testGetOriginalObject()
    {
        $this->assertNotEquals(SimpleModelService::class, get_class($this->service));
        $this->assertEquals(SimpleModelService::class, get_class($this->service->getOriginalObject()));
    }
}
