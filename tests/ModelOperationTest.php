<?php

namespace Bx\Model\Ext\Tests;

use Bx\Model\Ext\Common\ModelOperation;
use Bx\Model\Ext\Interfaces\ModelOperationInterface;
use Bx\Model\Ext\Tests\Samples\SimpleModel;
use Bx\Model\Ext\Tests\Samples\SimpleModelService;
use Data\Provider\Providers\ArrayDataProvider;
use PHPUnit\Framework\TestCase;

class ModelOperationTest extends TestCase
{
    /**
     * @var SimpleModelService
     */
    private $service;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $dataProvider = new ArrayDataProvider([
            ['ID' => 1, 'NAME' => 'first'],
            ['ID' => 2, 'NAME' => 'second'],
        ]);

        $this->service = new SimpleModelService($dataProvider);
    }

    public function testInitRemoveOperation()
    {
        $removeOperation = ModelOperation::initRemoveOperation(1, $this->service);
        $this->assertEquals(ModelOperation::class, get_class($removeOperation));
        $this->assertEquals(ModelOperationInterface::REMOVE_OPERATION, $removeOperation->getOperationType());
    }

    public function testHasValueKey()
    {
        $model = $this->service->getById(1);
        $saveOperation = ModelOperation::initUpdateOperation($model, $this->service, 'ID');
        $this->assertTrue($saveOperation->hasValueKey('ID'));
        $this->assertTrue($saveOperation->hasValueKey('NAME'));
        $this->assertFalse($saveOperation->hasValueKey('UNKNOWN'));
    }

    public function testGetOperationType()
    {
        $removeOperation = ModelOperation::initRemoveOperation(1, $this->service);
        $this->assertEquals(ModelOperationInterface::REMOVE_OPERATION, $removeOperation->getOperationType());
    }

    public function testIsFinished()
    {
        $removeOperation = ModelOperation::initRemoveOperation(1, $this->service);
        $this->assertFalse($removeOperation->isFinished());
        $removeOperation->commit();
        $this->assertTrue($removeOperation->isFinished());
    }

    public function testGetPkValue()
    {
        $removeOperation = ModelOperation::initRemoveOperation(1, $this->service);
        $this->assertEquals(1, $removeOperation->getPkValue());

        $model = $this->service->getById(2);
        $saveOperation = ModelOperation::initUpdateOperation($model, $this->service, 'ID');
        $this->assertEquals(2, $saveOperation->getPkValue());
    }

    public function testInitUpdateOperation()
    {
        $model = $this->service->getById(1);
        $saveOperation = ModelOperation::initUpdateOperation($model, $this->service, 'ID');
        $this->assertEquals(ModelOperation::class, get_class($saveOperation));
        $this->assertEquals(ModelOperationInterface::UPDATE_OPERATION, $saveOperation->getOperationType());
    }

    public function testGetValueByKey()
    {
        $model = $this->service->getById(1);
        $saveOperation = ModelOperation::initUpdateOperation($model, $this->service, 'ID');
        $this->assertEquals(1, $saveOperation->getValueByKey('ID'));
        $this->assertEquals('first', $saveOperation->getValueByKey('NAME'));
    }

    public function testJsonSerialize()
    {
        $removeOperation = ModelOperation::initRemoveOperation(1, $this->service);
        $this->assertEquals([
            'operation' => ModelOperationInterface::REMOVE_OPERATION,
            'pk' => 1,
            'model' => null,
        ], $removeOperation->jsonSerialize());
    }

    public function testGetService()
    {
        $removeOperation = ModelOperation::initRemoveOperation(1, $this->service);
        $this->assertEquals($this->service, $removeOperation->getService());
    }

    public function testCommit()
    {
        $this->assertNotNull($this->service->getById(1));
        $removeOperation = ModelOperation::initRemoveOperation(1, $this->service);
        $removeOperation->commit();
        $this->assertNull($this->service->getById(1));
    }

    public function testGetModel()
    {
        $removeOperation = ModelOperation::initRemoveOperation(1, $this->service);
        $this->assertNull($removeOperation->getModel());

        $model = $this->service->getById(2);
        $saveOperation = ModelOperation::initUpdateOperation($model, $this->service, 'ID');
        $this->assertEquals($model, $saveOperation->getModel());
    }

    public function testInitCreateOperation()
    {
        $newModel = new SimpleModel(['NAME' => 'some name']);
        $createOperation = ModelOperation::initCreateOperation($newModel, $this->service, 'ID');
        $this->assertEquals(ModelOperation::class, get_class($createOperation));
        $this->assertEquals(ModelOperationInterface::CREATE_OPERATION, $createOperation->getOperationType());
    }

    public function testAssertValueByKey()
    {
        $model = $this->service->getById(1);
        $saveOperation = ModelOperation::initUpdateOperation($model, $this->service, 'ID');
        $this->assertTrue($saveOperation->assertValueByKey('ID', 1));
        $this->assertTrue($saveOperation->assertValueByKey('NAME', 'first'));
    }
}
