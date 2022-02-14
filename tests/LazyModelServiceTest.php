<?php

namespace Bx\Model\Tests;

use Bx\Model\Ext\LazyModelService;
use Bx\Model\Ext\Tests\Samples\SimpleModel;
use Bx\Model\Ext\Tests\Samples\SimpleModelService;
use Data\Provider\Providers\ArrayDataProvider;
use PHPUnit\Framework\TestCase;

class LazyModelServiceTest extends TestCase
{
    /**
     * @var SimpleModelService
     */
    private $originalService;
    /**
     * @var LazyModelService
     */
    private $lazyModelService;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $dataProvider = new ArrayDataProvider([]);

        $this->originalService = new SimpleModelService($dataProvider);
        $this->lazyModelService = new LazyModelService($this->originalService, SimpleModel::class);
    }

    public function testGetOriginalObject()
    {
        $this->assertNotEquals($this->originalService, $this->lazyModelService);
        $this->assertEquals($this->originalService, $this->lazyModelService->getOriginalObject());
    }
}
