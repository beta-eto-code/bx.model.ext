<?php

namespace Bx\Model\Ext\Tests;

use Bx\Model\Ext\Common\OperationHolder;
use Bx\Model\Ext\Tests\Samples\SimpleModel;
use Bx\Model\Ext\Tests\Samples\SimpleModelService;
use Data\Provider\Providers\ArrayDataProvider;
use PHPUnit\Framework\TestCase;

class OperationHolderTest extends TestCase
{
    /**
     * @var OperationHolder
     */
    private $operationHolder;
    /**
     * @var SimpleModelService
     */
    private $service;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->operationHolder = new OperationHolder();
        $dataProvider = new ArrayDataProvider([
            ['ID' => 1, 'NAME' => 'first'],
            ['ID' => 2, 'NAME' => 'second'],
        ]);
        $this->service = new SimpleModelService($dataProvider);
    }

    public function testGetCreateOperationList()
    {
        $this->assertEquals([], iterator_to_array($this->operationHolder->getCreateOperationList()));
        $model1 = new SimpleModel(['NAME' => 'new1']);
        $model2 = new SimpleModel(['NAME' => 'new2']);
        $this->operationHolder->addOperationCreate($model1, $this->service, 'ID');
        $this->operationHolder->addOperationCreate($model2, $this->service, 'ID');
        $this->operationHolder->addOperationRemove(1, $this->service);

        $result = iterator_to_array($this->operationHolder->getCreateOperationList());
        $this->assertCount(2, $result);
        $this->assertEquals($model1, $result[0]->getModel());
        $this->assertEquals($model2, $result[1]->getModel());
    }

    public function testGetUpdateOperationList()
    {
        $this->assertEquals([], iterator_to_array($this->operationHolder->getUpdateOperationList()));
        $model1 = $this->service->getById(1);
        $model2 = $this->service->getById(2);
        $this->operationHolder->addOperationUpdate($model1, $this->service, 'ID');
        $this->operationHolder->addOperationUpdate($model2, $this->service, 'ID');
        $this->operationHolder->addOperationRemove(3, $this->service);

        $result = iterator_to_array($this->operationHolder->getUpdateOperationList());
        $this->assertCount(2, $result);
        $this->assertEquals($model1, $result[0]->getModel());
        $this->assertEquals($model2, $result[1]->getModel());

    }

    public function testGetOperationList()
    {
        $this->assertEquals([], iterator_to_array($this->operationHolder->getOperationList()));
        $model1 = $this->service->getById(1);
        $this->operationHolder->addOperationUpdate($model1, $this->service, 'ID');
        $model2 = new SimpleModel(['NAME' => 'new2']);
        $this->operationHolder->addOperationCreate($model2, $this->service, 'ID');

        $result = iterator_to_array($this->operationHolder->getOperationList());
        $this->assertCount(2, $result);
        $this->assertEquals($model2, $result[0]->getModel());
        $this->assertEquals($model1, $result[1]->getModel());
    }

    public function testGetRemoveOperationList()
    {
        $this->assertEquals([], iterator_to_array($this->operationHolder->getRemoveOperationList()));
        $this->operationHolder->addOperationRemove(1, $this->service);
        $this->operationHolder->addOperationRemove(2, $this->service);

        $result = iterator_to_array($this->operationHolder->getRemoveOperationList());
        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]->getPkValue());
        $this->assertEquals(2, $result[1]->getPkValue());
    }

    public function testCommit()
    {
        $this->operationHolder->addOperationRemove(1, $this->service);
        $this->operationHolder->addOperationRemove(2, $this->service);
        $this->assertEquals(2, $this->service->getCount([]));

        $this->operationHolder->commit();

        $this->assertEquals(0, $this->service->getCount([]));
    }

    public function testActualizeOperations()
    {
        $this->operationHolder->addOperationRemove(1, $this->service);
        $this->operationHolder->addOperationRemove(2, $this->service);
        $this->operationHolder->addOperationRemove(1, $this->service);

        $model1 = $this->service->getById(1);
        $this->operationHolder->addOperationUpdate($model1, $this->service, 'ID');
        $this->assertCount(4, $this->operationHolder->getOperationList());
        $this->operationHolder->actualizeOperations();
        $this->assertCount(2, $this->operationHolder->getOperationList());
    }

    public function testFlush()
    {
        $this->operationHolder->addOperationRemove(1, $this->service);
        $this->operationHolder->addOperationRemove(2, $this->service);
        $this->assertCount(2, $this->operationHolder->getOperationList());

        $this->operationHolder->flush();
        $this->assertCount(0, $this->operationHolder->getOperationList());
    }
}
