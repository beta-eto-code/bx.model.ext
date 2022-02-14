<?php

namespace Bx\Model\Ext\Tests\Samples;

use Bitrix\Main\Error;
use Bitrix\Main\Result;
use BX\Data\Provider\BxQueryAdapter;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\BaseModelService;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\ModelCollection;
use Data\Provider\Interfaces\CompareRuleInterface;
use Data\Provider\Interfaces\DataProviderInterface;
use Data\Provider\QueryCriteria;

class SimpleModelService extends BaseModelService
{
    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    public function __construct(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return array
     */
    static protected function getFilterFields(): array
    {
        return [];
    }

    /**
     * @param array $params
     * @param UserContextInterface|null $userContext
     * @return ModelCollection
     */
    public function getList(array $params, UserContextInterface $userContext = null): ModelCollection
    {
        $query = BxQueryAdapter::initFromArray($params)->getQuery();
        $collection = new ModelCollection([], SimpleModel::class);
        foreach ($this->dataProvider->getIterator($query) as $itemData) {
            $collection->add($itemData);
        }

        return $collection;
    }

    /**
     * @param array $params
     * @param UserContextInterface|null $userContext
     * @return int
     */
    public function getCount(array $params, UserContextInterface $userContext = null): int
    {
        $query = BxQueryAdapter::initFromArray($params)->getQuery();
        return $this->dataProvider->getDataCount($query);
    }

    /**
     * @param int $id
     * @param UserContextInterface|null $userContext
     * @return AbsOptimizedModel|null
     */
    public function getById(int $id, UserContextInterface $userContext = null): ?AbsOptimizedModel
    {
        return $this->getList(['filter' => ['=ID' => $id]])->first();
    }

    /**
     * @param int $id
     * @param UserContextInterface|null $userContext
     * @return Result
     */
    public function delete(int $id, UserContextInterface $userContext = null): Result
    {
        $query = new QueryCriteria();
        $query->addCriteria('ID', CompareRuleInterface::EQUAL, $id);
        $operationResult = $this->dataProvider->remove($query);
        $result = new Result();
        if ($operationResult->hasError()) {
            $result->addError(new Error($operationResult->getErrorMessage()));
        }

        return $result;
    }

    /**
     * @param AbsOptimizedModel $model
     * @param UserContextInterface|null $userContext
     * @return Result
     */
    public function save(AbsOptimizedModel $model, UserContextInterface $userContext = null): Result
    {
        $data = iterator_to_array($model);
        $query = null;
        if ((int)$model['ID'] > 0) {
            $query = new QueryCriteria();
            $query->addCriteria('ID', CompareRuleInterface::EQUAL, (int)$model['ID']);
        }

        $operationResult = $this->dataProvider->save($data, $query);
        $result = new Result();
        if ($operationResult->hasError()) {
            $result->addError(new Error($operationResult->getErrorMessage()));
        }

        return $result;
    }

    /**
     * @return array
     */
    static protected function getSortFields(): array
    {
        return [];
    }
}
