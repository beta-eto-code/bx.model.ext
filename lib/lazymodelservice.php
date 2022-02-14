<?php

namespace Bx\Model\Ext;

use Bx\Model\AbsOptimizedModel;
use Bx\Model\Ext\Interfaces\LazyModelInterface;
use Bx\Model\Interfaces\Models\ReadableModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\ModelCollection;
use Exception;

class LazyModelService extends ServiceDecorator
{
    /**
     * @var string
     */
    private $pkName;
    /**
     * @var string
     */
    private $modelClassName;

    public function __construct(
        ReadableModelServiceInterface $modelService,
        string $modelClassName,
        string $pkName = 'ID'
    ) {
        parent::__construct($modelService);
        $this->pkName = $pkName;
        $this->modelClassName = $modelClassName;
    }

    /**
     * @param array $params
     * @param UserContextInterface|null $userContext
     * @return ModelCollection
     * @throws Exception
     */
    public function getList(array $params, UserContextInterface $userContext = null): ModelCollection
    {
        $newCollection = new LazyModelCollection([], $this->modelClassName, $this->modelService, $this->pkName);
        $selectIsEmpty = empty($params['select']);
        $collection = $this->modelService->getList($params, $userContext);
        if ($collection->count() === 0) {
            return $newCollection;
        }

        foreach ($collection as $item) {
            $newCollection->append(
                $item instanceof LazyModelInterface ?
                    $item :
                    new LazyModel($item[$this->pkName], $item, $this, $selectIsEmpty)
            );
        }

        return $newCollection;
    }

    /**
     * @param int $id
     * @param UserContextInterface|null $userContext
     * @return AbsOptimizedModel|null
     * @throws Exception
     */
    public function getById(int $id, UserContextInterface $userContext = null): ?AbsOptimizedModel
    {
        $item = $this->modelService->getById($id, $userContext);
        if (empty($item)) {
            return null;
        }

        if (!($item instanceof LazyModelInterface)) {
            $selectIsEmpty = empty($params['select']);
            $item = new LazyModel($item[$this->pkName], $item, $this, $selectIsEmpty);
        }

        return $item;
    }
}
