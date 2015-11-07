<?php

/**
 * This is a generalization of Zend_Controller_Action_HelperBroker_PriorityStack.
 */
class Zefram_Stdlib_PriorityStack implements IteratorAggregate, ArrayAccess, Countable
{
    /**
     * @var array
     */
    protected $_itemsByPriority = array();

    /**
     * @var array
     */
    protected $_itemsByName = array();

    /**
     * @var int
     */
    protected $_nextDefaultPriority = 1;

    /**
     * @var bool
     */
    protected $_needsSorting = false;

    /**
     * Magic property overloading for returning item by name
     *
     * @param string $name  The item name
     * @return mixed
     */
    public function __get($name)
    {
        if (!array_key_exists($name, $this->_itemsByName)) {
            return false;
        }

        return $this->_itemsByName[$name];
    }

    /**
     * Magic property overloading for returning if item is set by name
     *
     * @param string $name  The item name
     * @return mixed
     */
    public function __isset($name)
    {
        return array_key_exists($name, $this->_itemsByName);
    }

    /**
     * Magic property overloading for unsetting item by name
     *
     * @param string $name  The item name
     * @return mixed
     */
    public function __unset($name)
    {
        return $this->offsetUnset($name);
    }

    /**
     * Push item onto the stack
     *
     * @param mixed $item
     * @param string $name OPTIONAL
     * @return Zefram_Stdlib_PriorityStack
     */
    public function push($item, $name = null)
    {
        $this->offsetSet($this->getNextFreeHigherPriority(), $item, $name);
        return $this;
    }

    /**
     * Return an iterable
     * @return array
     */
    public function getIterator()
    {
        if ($this->_needsSorting) {
            // make sure order by priority (from highest to lowest) is enforced
            krsort($this->_itemsByPriority);
            $this->_needsSorting = false;
        }
        return new ArrayObject($this->_itemsByPriority);
    }

    /**
     * offsetExists()
     *
     * @param int|string $priorityOrName
     * @return bool
     */
    public function offsetExists($priorityOrName)
    {
        if (is_string($priorityOrName)) {
            return array_key_exists($priorityOrName, $this->_itemsByName);
        } else {
            return array_key_exists($priorityOrName, $this->_itemsByPriority);
        }
    }

    /**
     * offsetGet()
     *
     * @param int|string $priorityOrName
     * @return Zefram_Stdlib_PriorityStack
     * @throws Exception
     */
    public function offsetGet($priorityOrName)
    {
        if (!$this->offsetExists($priorityOrName)) {
            throw new Exception(sprintf(
                'An item with priority or name %s does not exist',
                $priorityOrName
            ));
        }

        if (is_string($priorityOrName)) {
            return $this->_itemsByName[$priorityOrName];
        } else {
            return $this->_itemsByPriority[$priorityOrName];
        }
    }

    /**
     * offsetSet()
     *
     * @param int $priority
     * @param mixed $item
     * @param string $name OPTIONAL
     * @return Zefram_Stdlib_PriorityStack
     */
    public function offsetSet($priority, $item, $name = null)
    {
        $priority = (int) $priority;

        if ($name !== null) {
            $name = (string) $name;
            if (array_key_exists($name, $this->_itemsByName)) {
                throw new Exception(sprintf('An item with name %s already exists', $name));
            }
        }

        if (array_key_exists($priority, $this->_itemsByPriority)) {
            $priority = $this->getNextFreeHigherPriority($priority);  // ensures LIFO
            trigger_error(
                sprintf('A helper with the same priority already exists, reassigning to %d', $priority),
                E_USER_WARNING
            );
        }

        $this->_itemsByPriority[$priority] = $item;

        if ($name !== null) {
            $this->_itemsByName[$name] = $item;
        }

        if ($priority === ($nextFreeDefault = $this->getNextFreeHigherPriority($this->_nextDefaultPriority))) {
            $this->_nextDefaultPriority = $nextFreeDefault;
        }

        $this->_needsSorting = true;
        return $this;
    }

    /**
     * offsetUnset()
     *
     * @param int|string $priorityOrName Priority or the item name
     * @return Zefram_Stdlib_PriorityStack
     */
    public function offsetUnset($priorityOrName)
    {
        if (!$this->offsetExists($priorityOrName)) {
            throw new Exception(sprintf(
                'An item with priority or name %s does not exist',
                $priorityOrName
            ));
        }

        if (is_string($priorityOrName)) {
            $name = $priorityOrName;
            $item = $this->_itemsByName[$priorityOrName];
            $priority = array_search($item, $this->_itemsByPriority, true);
        } else {
            $priority = $priorityOrName;
            $item = $this->_itemsByPriority[$priorityOrName];
            $name = array_search($item, $this->_itemsByName, true);
        }

        unset($this->_itemsByPriority[$priority]);

        if (is_string($name)) {
            unset($this->_itemsByName[$name]);
        }

        return $this;
    }

    /**
     * Return the number of items
     *
     * @return int
     */
    public function count()
    {
        return count($this->_itemsByPriority);
    }

    /**
     * Find the next free higher priority.  If an index is given, it will
     * find the next free highest priority after it.
     *
     * @param int $indexPriority OPTIONAL
     * @return int
     */
    public function getNextFreeHigherPriority($indexPriority = null)
    {
        if ($indexPriority == null) {
            $indexPriority = $this->_nextDefaultPriority;
        }

        $priorities = array_keys($this->_itemsByPriority);

        while (in_array($indexPriority, $priorities)) {
            ++$indexPriority;
        }

        return $indexPriority;
    }

    /**
     * Find the next free lower priority.  If an index is given, it will
     * find the next free lower priority before it.
     *
     * @param int $indexPriority OPTIONAL
     * @return int
     */
    public function getNextFreeLowerPriority($indexPriority = null)
    {
        if ($indexPriority == null) {
            $indexPriority = $this->_nextDefaultPriority;
        }

        $priorities = array_keys($this->_itemsByPriority);

        while (in_array($indexPriority, $priorities)) {
            --$indexPriority;
        }

        return $indexPriority;
    }

    /**
     * Return the highest priority
     *
     * @return int
     */
    public function getHighestPriority()
    {
        return max(array_keys($this->_itemsByPriority));
    }

    /**
     * Return the lowest priority
     *
     * @return int
     */
    public function getLowestPriority()
    {
        return min(array_keys($this->_itemsByPriority));
    }

    /**
     * Return the items referenced by name
     *
     * @return array
     */
    public function getItemsByName()
    {
        return $this->_itemsByName;
    }
}
