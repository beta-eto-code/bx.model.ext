# Расширенное управление моделями bx.model

Данный модуль включает в себя ряд декораторов для сервисов моделей:

* LazyModelService - реализует механизм загрузки данных по требованию.
* StorageModelService - реализует механизм Indenty map, содержит хранилище с ранее запрошенными объектами.
* TransactionModelService - содержит хранилище операций записи/удаления, для создания общей транзакции.

## LazyModelService

Содержит декоратор сервиса моделей (LazyModelService), декоратор модели (LazyModel) и отдельную коллекцию (LazyModelCollection) для массовой загурзки данных по требованию.

**Пример работы**

```php
use Bx\Model\Ext\DataHelper;
use Bx\Model\Ext\LazyModelService;
use Bx\Model\Ext\LazyModel;
use Bitrix\Main\Loader;

Loader::includeModule('bx.model.ext');
$someServiceModel = new SomeServiceModel();     // некий сервис для работы с моделями
$lazyServiceModel = new LazyModelService(
    $someServiceModel,
    SomeModel::class,       // класс модели данных
    'ID'                    // первичный ключ
);

$collection = $lazyServiceModel->getList([
    'select' => ['ID'],
]);                             // [['ID' => 1], ['ID' => 2], ['ID' => 3]]

$firstModel = $collection->first();
$firstModel->load();            // запрашиваем все данные по данной модели
DataHelper::load($firstModel);  // альтернативный способ запроса данных модели

print_r(iterator_to_array($firstModel));    // ['ID' => 1, 'NAME' => 'first'...]
print_r(iterator_to_array($collection));    // [['ID' => 1, 'NAME' => 'first'...], ['ID' => 2], ['ID' => 3]]

$collection->load();            // запрашиваем все данные по моделям коллекции
DataHelper::load($collection);  // альтернативный способ запроса данных коллекции

print_r(iterator_to_array($collection));    // [['ID' => 1, 'NAME' => 'first'...], ['ID' => 2, 'NAME' => 'second'...], ['ID' => 3, 'NAME' => 'third'...]]

var_dump($first instanceof SomeModel);  // false
var_dump($first instanceof LazyModel);  // true

// данный метод позволяет получить инкапсулированную модель
var_dump($first->getOriginalObject() instanceof SomeModel); // true
/** 
 * альтернативный метод для запроса инкапсулированной модели, 
 * в отличии от первого метода позволяет пройти по всей цепочки 
 * декоратов и извлечь исходную модель 
 * **/
var_dump(DataHelper::extractOriginalObject($first) instanceof SomeModel); // true

var_dump($lazyServiceModel->getOriginalObject() instanceof SomeServiceModel); // true
var_dump(DataHelper::extractOriginalObject($lazyServiceModel) instanceof SomeServiceModel); // true
```

## StorageModelService

Содержит докоратор сервиса моделей (LazyModelService), который в свою очередь сохраняет список запрошенных моделей и при повторном запросе тех же моделей извлекает их из внутреннего хранилища. Таким образом можно избежать дублирования объектов и уменьшить количество обращений к источнику данных. Механизм хранилища по-умолчанию использует LRU кеш.

**Пример работы**

```php
use Bx\Model\Ext\StorageModelService;
use Bx\Model\Ext\Common\ModelStorage;
use Bx\Model\Ext\Common\LruRemoveStrategy;
use Bitrix\Main\Loader;

Loader::includeModule('bx.model.ext');

$someServiceModel = new SomeServiceModel();     // некий сервис для работы с моделями
$modelSotrage = new ModelStorage(               // создаем хранилище для моделей
    new LruRemoveStrategy(),                    // стратегия чистки кеша
    'ID',                                       // первичный ключ
    [],                                         // элементы для добавления в хранилище
    1000                                        // максимальное количество элементов в хранилище
);
$storageServiceModel = new StorageModelService(
    $someServiceModel,
    SomeModel::class,
    'ID',
    $modelSotrage
);

$model1 = $storageServiceModel->getById(1);
$model2 = $storageServiceModel->getById(1);
$model3 = $storageServiceModel->getList(['filter' => ['=ID' => 1]])->first();

var_dump($model1 === $model2);      // true, один и тот же объект
var_dump($model1 === $model3);      // true, один и тот же объект
```

## TransactionModelService

Содержит декоратор сервиса моделей (TransactionModelService) и декоратор моделей (StateModel), который позволяет отслеживать изменение данных в модели. Все опреции на запись и удаление собираются в отдельном хранилище операций, которые в последующем можно запустить как единую транзакцию и/или реализовать сценарий массового обновления данных или же реализовать паттерн Unit of Work.

**Пример работы**

```php
use Bx\Model\Ext\DataHelper;
use Bx\Model\Ext\TransactionModelService;
use Bx\Model\Ext\Common\OperationHolder;
use Bitrix\Main\Loader;

Loader::includeModule('bx.model.ext');
$someServiceModel = new SomeServiceModel();     // некий сервис для работы с моделями
$operationHolder = new OperationHolder();       // хранилище операций
$transactionServiceModel = new TransactionModelService(
    $operationHolder,
    $someServiceModel,
    SomeModel::class
);

$model = $transactionServiceModel->getById(1); // ['ID' => 1, 'NAME' => 'Original name']
$model->setName('Name 1');
$model['NAME'] = 'Name 2';

var_dump($model->isChanged());              //true

$state = $model->offsetGetState('NAME');    // хранище состояний указанного поля
print_r(iterator_to_array($state));         // ['Name 2', 'Name 1', 'Original name']

var_dump($model->getName());                // 'Name 2'
var_dump($model['NAME']);                   // 'Name 2'

$model->loadPrevState('NAME');              // загружаем предыдущее состояние поля

var_dump($model->getName());                // 'Name 1'
var_dump($model['NAME']);                   // 'Name 1'

$model->loadPrevState('NAME');              // загружаем предыдущее состояние поля

var_dump($model->getName());                // 'Original name'
var_dump($model['NAME']);                   // 'Original name'

$model->loadNexState('NAME');               // загружаем следующее состояние поля

var_dump($model->getName());                // 'Name 1'
var_dump($model['NAME']);                   // 'Name 1'

$model->loadOriginalState();                // загружаем исходное состояние всех полей модели

var_dump($model->getName());                // 'Original name'
var_dump($model['NAME']);                   // 'Original name'

$model->loadLastState();                    // загружаем последнее состояние всех полей модели

var_dump($model->getName());                // 'Name 2'
var_dump($model['NAME']);                   // 'Name 2'

// запрашиваем измененные данные
var_dump($model->getChangesData());         // ['NAME' => 'NAME 2']

$result = $transactionServiceModel->save($model);     // получаем экземпляр TransactionResult, данная операция добавлена в хранилище операций, но не была применена
$result->commit();              // мы можем применить данную операцию на месте
DataHelper::commit($result);    // или применить ее таким способом

// запрос всех опреаций для указанного сервиса
$operationHolder->getOperationList(SomeServiceModel::class);
// запрос опреаций создания для указанного сервиса
$operationHolder->getCreateOperationList(SomeServiceModel::class); 
// запрос опреаций обновления для указанного сервиса
$operationHolder->getUpdateOperationList(SomeServiceModel::class); 
// запрос опреаций создания для указанного сервиса
$operationHolder->getRemoveOperationList(SomeServiceModel::class);

$operationHolder->actualizeOperations();    // данный метод позволяет отчистить хранилище от ранее выполненных операции и устанить конфликтующие операции

$operationHolder->commit();             // выполнить все опреции из хранилища и отчистить хранилище
DataHelper::commit($operationHolder);   // альтернативный метод выполнения операций из хранилища
```