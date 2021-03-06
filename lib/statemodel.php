<?php

namespace Bx\Model\Ext;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use SplDoublyLinkedList;

/**
 * @psalm-suppress UndefinedDocblockClass
 */
class StateModel extends ModelDecorator
{
    /**
     * @var SplDoublyLinkedList[]
     */
    private $stateStorage = [];

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function offsetSet($offset, $value)
    {
        $this->initNewState($offset, $value);
        parent::offsetSet($offset, $value);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @param mixed $prevValue
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    private function initNewState($offset, $value, $prevValue = null)
    {
        if (!(($this->stateStorage[$offset] ?? null) instanceof SplDoublyLinkedList)) {
            $this->stateStorage[$offset] = new SplDoublyLinkedList();
            $this->stateStorage[$offset]->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);
            $this->stateStorage[$offset]->push($prevValue ?? $this->offsetGet($offset));
        }

        $this->stateStorage[$offset]->push($value);
        $this->stateStorage[$offset]->rewind();
    }

    /**
     * @param mixed $offset
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function offsetUnset($offset)
    {
        $this->initNewState($offset, null);
        parent::offsetUnset($offset);
    }

    /**
     * @param mixed $offset
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function loadPrevState($offset)
    {
        $state = $this->offsetGetState($offset);
        if (empty($state)) {
            return;
        }

        if ($state->key() > 0) {
            $state->next();
            parent::offsetSet($offset, $state->current());
        }
    }

    /**
     * @param mixed $offset
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function loadNextState($offset)
    {
        $state = $this->offsetGetState($offset);
        if (empty($state)) {
            return;
        }

        if ($state->key() < ($state->count() - 1)) {
            $state->prev();
            parent::offsetSet($offset, $state->current());
        }
    }

    /**
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function loadOriginalState()
    {
        foreach ($this->stateStorage as $offset => $state) {
            while ($state->key() > 0) {
                $this->loadPrevState($offset);
            }
        }
    }

    /**
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    public function loadLastState()
    {
        foreach ($this->stateStorage as $offset => $state) {
            while ($state->key() < ($state->count() - 1)) {
                $this->loadNextState($offset);
            }
        }
    }

    /**
     * @return bool
     */
    public function isChanged(): bool
    {
        return !empty($this->stateStorage);
    }

    /**
     * @param mixed $offset
     * @return SplDoublyLinkedList|null
     */
    public function offsetGetState($offset): ?SplDoublyLinkedList
    {
        if (($this->stateStorage[$offset] ?? null) instanceof SplDoublyLinkedList) {
            return $this->stateStorage[$offset];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getChangesData(): array
    {
        $result = [];
        foreach ($this->stateStorage as $key => $state) {
            $value = $this->offsetGet($key);
            if ($value !== $state->bottom()) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws ArgumentException
     * @throws SystemException
     */
    public function __call($method, $args)
    {
        $oldModel = iterator_to_array($this->model);
        $result = parent::__call($method, $args);
        foreach ($this->model as $key => $value) {
            if ($oldModel[$key] !== $value) {
                $this->initNewState($key, $value, $oldModel[$key]);
            }
        }

        return $result;
    }
}
