<?php

namespace Carica\Io {

  class Deferred {

    const STATE_PENDING = 'pending';
    const STATE_RESOLVED = 'resolved';
    const STATE_REJECTED = 'rejected';

    private $_state = self::STATE_PENDING;
    private $_done = NULL;
    private $_failed = NULL;
    private $_progress = NULL;

    public function __construct() {
      $this->_done = new Io\Callbacks();
      $this->_failed = new Io\Callbacks();
      $this->_progress = new Io\Callbacks();
    }

    public function always(Callable $callback) {
      $this->_done->add($callback);
      $this->_failed->add($callback);
      return $this;
    }

    public function done(Callable $callback) {
      $this->_done->add($callback);
      return $this;
    }

    public function fail(Callable $callback) {
      $this->_failed->add($callback);
      return $this;
    }

    public function isRejected() {
      return $this->_state = self::STATE_REJECTED;
    }

    public function isResolved() {
      return $this->_state = self::STATE_RESOLVED;
    }

    public function notify() {
      call_user_func_array($this->_progress, func_get_args());
    }

    public function pipe(
      Callable $doneFilter = NULL,
      Callable $failFilter = NULL,
      Callable $progressFilter = NULL
    ) {

      return $defer->promise();
    }

    public function progress(Callable $callback) {
      $this->_progress->add($callback);
      return $this;
    }

    public function promise() {
      return new Deferred\Promise($this);
    }

    public function reject() {
      $this->_state = self::STATE_REJECTED;
      call_user_func_array($this->_failed, func_get_args());
    }

    public function resolve() {
      $this->_state = self::STATE_RESOLVED;
      call_user_func_array($this->_done, func_get_args());
    }

    public function state() {
      return $this->_state;
    }

    public function then($done = NULL, $fail = NULL, $progress = NULL) {
      $this->addCallbacksIfProvided(array($this, 'done'), $done);
      $this->addCallbacksIfProvided(array($this, 'fail'), $fail);
      $this->addCallbacksIfProvided(array($this, 'progress'), $progress);
    }

    private function addCallbacksIfProvided($add, $callbacks) {
      if (is_callable($callbacks)) {
        $add($callbacks);
      } elseif (is_array($done)) {
        foreach ($done as $callback) {
          $add($callbacks);
        }
      }
    }
  }

  function Deferred() {
    return new Deferred();
  }

  function when() {
    $promises = func_get_args();

  }
}
