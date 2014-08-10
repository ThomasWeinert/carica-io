<?php

namespace Carica\Io {

  /**
   * Manage a list Callable elements, the list can be uses as one callable, too.
   *
   * @property Callable add
   * @property Callable remove
   * @property Callable fire
   * @property Callable lock
   * @property Callable disable
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
     * @return Callbacks
     */
    public function add(Callable $callback) {
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
     * @return Callbacks
     */
    public function remove(Callable $callback) {
      $hash = $this->getCallableHash($callback);
      if (!$this->_locked && isset($this->_callbacks[$hash])) {
        unset($this->_callbacks[$hash]);
      }
      return $this;
    }

    /**
     * Remove all callbacks
     *
     * @return Callbacks
     */
    public function clear() {
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
    public function has(Callable $callback) {
      $hash = $this->getCallableHash($callback);
      return isset($this->_callbacks[$hash]);
    }

    /**
     * Lock the list, do now allow changes any more.
     *
     * @return Callbacks
     */
    public function lock() {
      $this->_locked = TRUE;
      return $this;
    }

    /**
     * Validate if the list is locked
     *
     * @return boolean
     */
    public function locked() {
      return $this->_locked;
    }

    /**
     * Disable the execution of the callbacks in the list
     *
     * @return Callbacks
     */
    public function disable() {
      $this->_disabled = TRUE;
      return $this;
    }

    /**
     * Validate if the callbacks are disabled
     *
     * @return boolean
     */
    public function disabled() {
      return $this->_disabled;
    }

    /**
     * Execute the callbacks
     *
     * @param mixed [$argument,...]
     */
    public function fire() {
      if (!$this->_disabled) {
        $this->_fired = TRUE;
        $arguments = func_get_args();
        foreach ($this->_callbacks as $callback) {
          call_user_func_array($callback, $arguments);
        }
      }
    }

    /**
     * Validate if the callbacks were executed at least once
     *
     * @return boolean
     */
    public function fired() {
      return $this->_fired;
    }

    /**
     * If the object is used as an functor, call fire()
     *
     * @param mixed [$argument,...]
     * @return mixed
     */
    public function __invoke() {
      return call_user_func_array(array($this, 'fire'), func_get_args());
    }

    /**
     * Allow to fetch the methods of this object as anonymous functions
     *
     * @throws \LogicException
     * @param string $name
     * @return \Callable
     */
    public function __get($name) {
      if (method_exists($this, $name)) {
        $callback = array($this, $name);
        return function() use ($callback) {
          call_user_func_array($callback, func_get_args());
        };
      }
      throw new \LogicException('Unknown property: '.$name);
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
    public function getIterator() {
      return new \ArrayIterator(array_values($this->_callbacks));
    }

    /**
     * Countable interface, return the number of stored callbacks
     *
     * @see Countable::count()
     * @return integer
     */
    public function count() {
      return count($this->_callbacks);
    }

    /**
     * Get an hash for the provided callable/object
     *
     * @param Callable|object $callable
     * @return string
     */
    private function getCallableHash($callable) {
      if (is_object($callable)) {
        return spl_object_hash($callable);
      } elseif (is_array($callable)) {
        return md5($this->getCallableHash($callable[0]), $callable[1]);
      } else {
        return md5((string)$callable);
      }
    }
  }
}