<?php

namespace Bx\Model\Ext\Tests;

use Bx\Model\Ext\Common\OperationHolder;
use Bx\Model\Ext\StateModel;
use Bx\Model\Ext\Tests\Samples\SimpleModel;
use Bx\Model\Ext\Tests\Samples\SimpleModelService;
use Bx\Model\Ext\TransactionModelService;
use Data\Provider\Providers\ArrayDataProvider;
use PHPUnit\Framework\TestCase;
use SplDoublyLinkedList;

class StateModelTest extends TestCase
{
    /**
     * @var \Bx\Model\AbsOptimizedModel|StateModel
     */
    private $model;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $dataProvider = new ArrayDataProvider([
            ['ID' => 1, 'NAME' => 'first'],
            ['ID' => 2, 'NAME' => 'second'],
        ]);
        $operationHolder = new OperationHolder();
        $service = new SimpleModelService($dataProvider);
        $service = new TransactionModelService($operationHolder, $service, SimpleModel::class);
        $this->model = $service->getById(1);
    }

    public function testLoadOriginalState()
    {
        $this->model['NAME'] = 'new value';
        $this->assertEquals('new value', $this->model['NAME']);
        $this->model->loadOriginalState();
        $this->assertEquals('first', $this->model['NAME']);
    }

    public function testIsChanged()
    {
        $this->assertFalse($this->model->isChanged());
        $this->model['NAME'] = 'new value';
        $this->assertTrue($this->model->isChanged());
    }

    public function testOffsetGetState()
    {
        $this->assertEquals(null, $this->model->offsetGetState('NAME'));
        $this->model['NAME'] = 'new value';
        $this->assertEquals(SplDoublyLinkedList::class, get_class($this->model->offsetGetState('NAME')));
    }

    public function testGetChangesData()
    {
        $this->assertEquals([], $this->model->getChangesData());
        $this->model['NAME'] = 'new value 1';
        $this->assertEquals(['NAME' => 'new value 1'], $this->model->getChangesData());
        $this->model['NAME'] = 'new value 2';
        $this->assertEquals(['NAME' => 'new value 2'], $this->model->getChangesData());
        $this->model->loadPrevState('NAME');
        $this->assertEquals(['NAME' => 'new value 1'], $this->model->getChangesData());
    }

    public function testLoadPrevState()
    {
        $this->model['NAME'] = 'new value';
        $this->assertEquals('new value', $this->model['NAME']);
        $this->model->loadPrevState('NAME');
        $this->assertEquals('first', $this->model['NAME']);
        $this->model->loadPrevState('NAME');
        $this->assertEquals('first', $this->model['NAME']);
    }

    public function testLoadNextState()
    {
        $this->model['NAME'] = 'new value';
        $this->assertEquals('new value', $this->model['NAME']);
        $this->model->loadPrevState('NAME');
        $this->assertEquals('first', $this->model['NAME']);
        $this->model->loadNextState('NAME');
        $this->assertEquals('new value', $this->model['NAME']);
        $this->model->loadNextState('NAME');
        $this->assertEquals('new value', $this->model['NAME']);
    }

    public function testLoadLastState()
    {
        $this->model['NAME'] = 'new value1';
        $this->model['NAME'] = 'new value2';
        $this->model['NAME'] = 'new value3';

        $this->model->loadPrevState('NAME');
        $this->model->loadPrevState('NAME');
        $this->model->loadPrevState('NAME');

        $this->assertEquals('first', $this->model['NAME']);

        $this->model->loadLastState();

        $this->assertEquals('new value3', $this->model['NAME']);
    }
}
