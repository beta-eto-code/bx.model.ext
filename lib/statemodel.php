<?php

namespace Bx\Model\Ext;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use SplDoublyLinkedList;

class StateModel extends ModelDecorator
{
    /**
     * @var SplDoublyLinkedList[]
     */
    private $stateStorage = [];

    /**
     * @param $offset
     * @param $value
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
     * @param $offset
     * @param $value
     * @param $prevValue
     * @return void
     * @throws ArgumentException
     * @throws SystemException
     */
    private function initNewState($offset, $value, $prevValue = null)
    {
        if (!($this->stateStorage[$offset] instanceof SplDoublyLinkedList)) {
            $this->stateStorage[$offset] = new SplDoublyLinkedList();
            $this->stateStorage[$offset]->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);
            $this->stateStorage[$offset]->push($prevValue ?? $this->offsetGet($offset));
        }

        $this->stateStorage[$offset]->push($value);
        $this->stateStorage[$offset]->rewind();
    }

    /**
     * @param $offset
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
     * @param $offset
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
     * @param $offset
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
            while($state->key() > 0) {
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
            while($state->key() > 0) {
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
     * @param $offset
     * @return SplDoublyLinkedList|null
     */
    public function offsetGetState($offset): ?SplDoublyLinkedList
    {
        if ($this->stateStorage[$offset] instanceof SplDoublyLinkedList) {
            return $this->stateStorage[$offset];
        }

        return null;
    }

    /**
     * @return array
     */
    public function getChangesData(): array
    {
        $resutl = [];
        foreach($this->stateStorage as $key => $state) {
            $result[$key] = $state->top();
        }

        return $result;
    }

    /**
     * @param $method
     * @param $args
     * @return void
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
