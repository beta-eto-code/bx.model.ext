<?php

namespace Bx\Model\Tests;

use Bx\Model\Ext\LazyModel;
use Bx\Model\Ext\LazyModelCollection;
use Bx\Model\Ext\LazyModelService;
use Bx\Model\Ext\Tests\Samples\SimpleModel;
use Bx\Model\Ext\Tests\Samples\SimpleModelService;
use Bx\Model\ModelCollection;
use Data\Provider\Providers\ArrayDataProvider;
use PHPUnit\Framework\TestCase;

class LazyModelCollectionTest extends TestCase
{
    /**
     * @var LazyModelCollection|LazyModel[]
     */
    private $collection;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $dataProvider = new ArrayDataProvider([
            ['ID' => 1, 'NAME' => 'first'],
            ['ID' => 2, 'NAME' => 'second'],
        ]);

        $service = new SimpleModelService($dataProvider);
        $service = new LazyModelService($service, SimpleModel::class);
        $this->collection = $service->getList([
            'select' => [
                'ID'
            ],
        ]);
    }

    public function testIsLoaded()
    {
        $this->assertFalse($this->collection->isLoaded());
        $this->collection->load();
        $this->assertTrue($this->collection->isLoaded());
    }

    public function testGetOriginalObject()
    {
        $this->assertEquals(LazyModelCollection::class, get_class($this->collection));

        $originalCollection = $this->collection->getOriginalObject();
        $this->assertEquals(ModelCollection::class, get_class($originalCollection));
        $this->assertEquals(SimpleModel::class, get_class($originalCollection->first()));
    }
}
