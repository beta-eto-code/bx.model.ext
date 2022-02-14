<?php

namespace Bx\Model\Tests;

use Bx\Model\Ext\LazyModel;
use Bx\Model\Ext\LazyModelService;
use Bx\Model\Ext\Tests\Samples\SimpleModel;
use Bx\Model\Ext\Tests\Samples\SimpleModelService;
use Data\Provider\Providers\ArrayDataProvider;
use Exception;
use PHPUnit\Framework\TestCase;

class LazyModelTest extends TestCase
{
    /**
     * @var LazyModel
     */
    private $model;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $dataProvider = new ArrayDataProvider([
            ['ID' => 1, 'NAME' => 'first'],
            ['ID' => 2, 'NAME' => 'second'],
        ]);

        $service = new SimpleModelService($dataProvider);
        $service = new LazyModelService($service, SimpleModel::class);
        $collection = $service->getList([
            'select' => [
                'ID'
            ],
        ]);

        $this->model = $collection->first();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testLoadFrom()
    {
        $data = ['ID' => 1, 'NAME' => 'new name'];
        $this->assertEquals(['ID' => 1], iterator_to_array($this->model));
        $this->model->loadFrom(new SimpleModel(['ID' => 1, 'NAME' => 'new name']));
        $this->assertEquals($data, iterator_to_array($this->model));
    }

    public function testLoad()
    {
        $this->assertEquals(['ID' => 1], iterator_to_array($this->model));
        $this->model->load();
        $this->assertEquals(['ID' => 1, 'NAME' => 'first'], iterator_to_array($this->model));
    }

    public function testIsLoaded()
    {
        $this->assertFalse($this->model->isLoaded());
        $this->model->load();
        $this->assertTrue($this->model->isLoaded());
    }

    public function testGetPkValue()
    {
        $this->assertEquals(1, $this->model->getPkValue());
    }
}
