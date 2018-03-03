<?php

namespace Carica\Io {

  /**
   * Manage a list Callable elements, the list can be uses as one callable, too.
   *
   * @property-read Callable add
   * @property-read Callable remove
   * @property-read Callable fire
   * @property-read Callable lock
   * @property-read Callable disable
   */
  class Callbacks implements \IteratorAggregate, \Countable {

    private $_callbacks = array();
    private $_disabled = FALSE;
    private $_locked = FALSE;
    private $_fired = FALSE;

    /**
     * Add a new callable, and return itself for chaining
     *
     * @param callable $callback
     * @return $this
     */
    public function add(Callable $callback): self {
      $hash = $this->getCallableHash($callback);
      if (!($this->_locked || isset($this->_callbacks[$hash]))) {
        $this->_callbacks[$hash] = $callback;
      }
      return $this;
    }

    /**
     * Remove an callable, and return itself for chaining
     *
     * @param callable $callback
     * @return $this
     */
    public function remove(Callable $callback): self {
      $hash = $this->getCallableHash($callback);
      if (!$this->_locked && isset($this->_callbacks[$hash])) {
        unset($this->_callbacks[$hash]);
      }
      return $this;
    }

    /**
     * Remove all callbacks
     *
     * @return $this
     */
    public function clear(): self {
      if (!$this->_locked) {
        $this->_callbacks = array();
      }
      return $this;
    }

    /**
     * Validate if the given callable is ion the list.
     *
     * @param callable $callback
     * @return boolean
     */
    public function has(Callable $callback): bool {
      $hash = $this->getCallableHash($callback);
      return isset($this->_callbacks[$hash]);
    }

    /**
     * Lock the list, do now allow changes any more.
     *
     * @return $this
     */
    public function lock(): self {
      $this->_locked = TRUE;
      return $this;
    }

    /**
     * Validate if the list is locked
     *
     * @return bool
     */
    public function locked(): bool {
      return $this->_locked;
    }

    /**
     * Disable the execution of the callbacks in the list
     *
     * @return $this
     */
    public function disable(): self {
      $this->_disabled = TRUE;
      return $this;
    }

    /**
     * Validate if the callbacks are disabled
     *
     * @return bool
     */
    public function disabled(): bool {
      return $this->_disabled;
    }

    /**
     * Execute the callbacks
     *
     * @param mixed [$argument,...]
     */
    public function fire(...$arguments) {
      if (!$this->_disabled) {
        $this->_fired = TRUE;
        foreach ($this->_callbacks as $callback) {
          $callback(...$arguments);
        }
      }
    }

    /**
     * Validate if the callbacks were executed at least once
     *
     * @return bool
     */
    public function fired(): bool {
      return $this->_fired;
    }

    /**
     * If the object is used as an functor, call fire()
     *
     * @param mixed [$argument,...]
     * @return mixed
     */
    public function __invoke(...$arguments) {
      return $this->fire(...$arguments);
    }

    /**
     * Allow to fetch the methods of this object as anonymous functions
     *
     * @throws \LogicException
     * @param string $name
     * @return callable
     */
    public function __get($name) {
      if (method_exists($this, $name)) {
        $callback = array($this, $name);
        return function(...$arguments) use ($callback) {
          $callback(...$arguments);
        };
      }
      throw new \LogicException('Unknown property: '.$name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
      return method_exists($this, $name);
    }

    /**
     * Block changes to the object properties
     *
     * @param string $name
     * @param mixed $value
     *
     * @throws \LogicException
     */
    public function __set($name, $value) {
      throw new \LogicException('Unknown/Readonly property: '.$name);
    }

    /**
     * IteratorAggregate interface for the stored callbacks
     *
     * @see IteratorAggregate::getIterator()
     * @return \Iterator
     */
    public function getIterator(): \Iterator {
      return new \ArrayIterator(array_values($this->_callbacks));
    }

    /**
     * Countable interface, return the number of stored callbacks
     *
     * @see Countable::count()
     * @return int
     */
    public function count(): int {
      return \count($this->_callbacks);
    }

    /**
     * Get an hash for the provided callable/object
     *
     * @param Callable|object $callable
     * @return string
     */
    private function getCallableHash($callable) {
      if (\is_object($callable)) {
        return spl_object_hash($callable);
      } elseif (\is_array($callable)) {
        return md5($this->getCallableHash($callable[0]), $callable[1]);
      } else {
        return md5((string)$callable);
      }
    }
  }
}
