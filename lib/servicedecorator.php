<?php

namespace Bx\Model\Ext;

use Bitrix\Main\Result;
use Bx\Model\AbsOptimizedModel;
use Bx\Model\Ext\Interfaces\DecoratorInterface;
use Bx\Model\Interfaces\DerivativeModelInterface;
use Bx\Model\Interfaces\ModelCollectionInterface;
use Bx\Model\Interfaces\ModelQueryInterface;
use Bx\Model\Interfaces\Models\QueryableModelServiceInterface;
use Bx\Model\Interfaces\Models\ReadableModelServiceInterface;
use Bx\Model\Interfaces\ModelServiceInterface;
use Bx\Model\Interfaces\UserContextInterface;
use Bx\Model\Interfaces\Models\FilterableInterface;
use Bx\Model\Interfaces\Models\LimiterInterface;
use Bx\Model\Interfaces\Models\SortableInterface;
use Bx\Model\Interfaces\Models\SaveableModelServiceInterface;
use Bx\Model\Interfaces\Models\RemoveableModelServiceInterface;
use Bx\Model\ModelCollection;
use Exception;

abstract class ServiceDecorator implements ModelServiceInterface, DecoratorInterface
{
    /**
     * @var ReadableModelServiceInterface
     */
    protected $modelService;

    public function __construct(ReadableModelServiceInterface $service)
    {
        $this->modelService = $service;
    }

    /**
     * @return ReadableModelServiceInterface
     */
    public function getOriginalObject(): object
    {
        return $this->modelService;
    }

    /**
     * Получаем построить запроса
     * @param UserContextInterface|null $userContext
     * @return ModelQueryInterface
     * @throws Exception
     */
    public function query(UserContextInterface $userContext = null): ModelQueryInterface
    {
        if ($this->modelService instanceof QueryableModelServiceInterface) {
            return $this->modelService->query($userContext);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('query', [$userContext]);
        }

        throw new Exception('Not implemented!');
    }

    /**
     * Список элементов
     * @param array $params
     * @param UserContextInterface|null $userContext
     * @return ModelCollection
     */
    public function getList(array $params, UserContextInterface $userContext = null): ModelCollection
    {
        return $this->modelService->getList($params, $userContext);
    }

    /**
     * Количество элементов
     * @param array $params
     * @param UserContextInterface|null $userContext
     * @return int
     */
    public function getCount(array $params, UserContextInterface $userContext = null): int
    {
        return $this->modelService->getCount($params, $userContext);
    }

    /**
     * Получаем элемент по идентификтору
     * @param int $id
     * @param UserContextInterface|null $userContext
     * @return AbsOptimizedModel|null
     */
    public function getById(int $id, UserContextInterface $userContext = null): ?AbsOptimizedModel
    {
        return $this->modelService->getById($id, $userContext);
    }

    /**
     * Получаем коллекцию производных моделей
     * @param string $class
     * @param array|null $filter
     * @param array|null $sort
     * @param int|null $limit
     * @return DerivativeModelInterface[]|ModelCollectionInterface
     */
    public function getModelCollection(
        string $class,
        array $filter = null,
        array $sort = null,
        int $limit = null
    ): ModelCollectionInterface {
        return $this->modelService->getModelCollection($class, $filter, $sort, $limit);
    }

    /**
     * Проверяет разрешено ли фитьтровать элементы по указаному полю
     * (используется в построителе запроса ModelQueryInterface)
     * @param string $fieldName
     * @return bool
     * @throws Exception
     */
    public function allowForFilter(string $fieldName): bool
    {
        if ($this->modelService instanceof FilterableInterface) {
            return $this->modelService->allowForFilter($fieldName);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('allowForFilter', [$fieldName]);
        }

        throw new Exception('Not implemented!');
    }

    /**
     * Возвращает фильтр собранный из переданных данных (используется в построителе запросов ModelQueryInterface)
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getFilter(array $params): array
    {
        if ($this->modelService instanceof FilterableInterface) {
            return $this->modelService->getFilter($params);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('getFilter', $params);
        }

        throw new Exception('Not implemented!');
    }

    /**
     * Проверяет разрешено ли сортировать по указанному полю (используется в построителе запроса ModelQueryInterface)
     * @param string $fieldName
     * @return bool
     * @throws Exception
     */
    public function allowForSort(string $fieldName): bool
    {
        if ($this->modelService instanceof SortableInterface) {
            return $this->modelService->allowForSort($fieldName);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('allowForSort', $fieldName);
        }

        throw new Exception('Not implemented!');
    }

    /**
     * Возвращает правило сортировки из переданных данных (используется в построителе запроса ModelQueryInterface)
     * @param array $params
     * @return array
     * @throws Exception
     */
    public function getSort(array $params): array
    {
        if ($this->modelService instanceof SortableInterface) {
            return $this->modelService->getSort($params);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('getSort', $params);
        }

        throw new Exception('Not implemented!');
    }

    /**
     * Возвращает значение максимального количества элементов выводимого на странице
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function getLimit(array $params): int
    {
        if ($this->modelService instanceof LimiterInterface) {
            return $this->modelService->getLimit($params);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('getLimit', $params);
        }

        throw new Exception('Not implemented!');
    }

    /**
     * Возвращает номер текущей страницы
     * @param array $params
     * @return int
     * @throws Exception
     */
    public function getPage(array $params): int
    {
        if ($this->modelService instanceof LimiterInterface) {
            return $this->modelService->getPage($params);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('getPage', $params);
        }

        throw new Exception('Not implemented!');
    }

    /**
     * Сохраняем модель в базе
     * @param AbsOptimizedModel $model
     * @param UserContextInterface|null $userContext
     * @return mixed
     * @throws Exception
     */
    public function save(AbsOptimizedModel $model, UserContextInterface $userContext = null): Result
    {
        if ($this->modelService instanceof SaveableModelServiceInterface) {
            return $this->modelService->save($model, $userContext);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('save', [$model, $userContext]);
        }

        throw new Exception('Not implemented!');
    }


    /**
     * Удаляем элемент по идентификатору
     * @param int $id
     * @param UserContextInterface|null $userContext
     * @return mixed
     * @throws Exception
     */
    public function delete(int $id, UserContextInterface $userContext = null): Result
    {
        if ($this->modelService instanceof RemoveableModelServiceInterface) {
            return $this->modelService->delete($id, $userContext);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call('delete', [$id, $userContext]);
        }

        throw new Exception('Not implemented!');
    }

    public function __call(string $method, array $args)
    {
        if (method_exists($this->modelService, $method)) {
            return $this->modelService->{$method}(...$args);
        }

        if (method_exists($this->modelService, '__call')) {
            return $this->modelService->__call($method, $args);
        }
    }
}
