<?php

namespace Carica\Io\Deferred {

  use Carica\Io;

  class Promise {

    protected $_defer = NULL;

    /**
     * Create the promise for a Deferred object.
     *
     * @param Io\Deferred $defer
     */
    public function __construct(Io\Deferred $defer) {
      $this->_defer = $defer;
    }

    /**
     * Add a callback that will be execute if the object is finalized with
     * resolved or reject
     *
     * @param Callable $callback
     * @return \Carica\Io\Deferred\Promise
     */
    public function always(Callable $callback) {
      $this->_defer->always($callback);
      return $this;
    }

    /**
     * Add a callback that will be executed if the object is resolved
     *
     * @param Callable $callback
     * @return \Carica\Io\Deferred\Promise
     */
    public function done(Callable $callback) {
      $this->_defer->done($callback);
      return $this;
    }

    /**
     * Add a callback that will be executed if the object was rejected
     *
     * @param Callable $callback
     * @return \Carica\Io\Deferred\Promise
     */
    public function fail(Callable $callback) {
      $this->_defer->fail($callback);
      return $this;
    }

    /**
     * Add a callback that will be executed if the object is notified about progress
     *
     * @param Callable $callback
     * @return \Carica\Io\Deferred\Promise
     */
    public function progress(Callable $callback) {
      $this->_defer->progress($callback);
      return $this;
    }

    /**
     * Return the state string. Here are constants for each state, too.
     *
     * @return string
     */
    public function state() {
      return $this->_defer->state();
    }

    /**
     * Filter and/or chain Deferreds.
     *
     * @param Callable $doneFilter
     * @param Callable $failFilter
     * @param Callable $progressFilter
     * @return \Carica\Io\Deferred\Promise
     */
    public function then(
      Callable $doneFilter = NULL,
      Callable $failFilter = NULL,
      Callable $progressFilter = NULL
    ) {
      return $this->_defer->then($doneFilter, $failFilter, $progressFilter);
    }
  }
}