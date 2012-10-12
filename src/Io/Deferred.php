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
    private $_arguments = array();

    public function __construct() {
      $this->_done = new Callbacks();
      $this->_failed = new Callbacks();
      $this->_progress = new Callbacks();
    }

    public function always(Callable $callback) {
      $this->_done->add($callback);
      $this->_failed->add($callback);
      if ($this->_state != self::STATE_PENDING) {
        call_user_func_array($callback, $this->_arguments);
      }
      return $this;
    }

    public function done(Callable $callback) {
      $this->_done->add($callback);
      if ($this->_state == self::STATE_RESOLVED) {
        call_user_func_array($callback, $this->_arguments);
      }
      return $this;
    }

    public function fail(Callable $callback) {
      $this->_failed->add($callback);
      if ($this->_state == self::STATE_REJECTED) {
        call_user_func_array($callback, $this->_arguments);
      }
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
      $defer = new Deferred();
      $this->done(
        function () use ($defer, $doneFilter){
          if ($doneFilter) {
            $defer->resolve(call_user_func_array($doneFilter, func_get_args()));
          } else {
            call_user_func_array(array($defer, 'resolve'), func_get_args());
          }
        }
      );
      $this->fail(
        function () use ($defer, $failFilter){
          if ($failFilter) {
            $defer->reject(call_user_func_array($failFilter, func_get_args()));
          } else {
            call_user_func_array(array($defer, 'reject'), func_get_args());
          }
        }
      );
      $this->progress(
        function () use ($defer, $progressFilter){
          if ($progressFilter) {
            $defer->notify(call_user_func_array($progressFilter, func_get_args()));
          } else {
            call_user_func_array(array($defer, 'notify'), func_get_args());
          }
        }
      );
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
      $this->_arguments = func_get_args();
      $this->_state = self::STATE_REJECTED;
      call_user_func_array($this->_failed, func_get_args());
    }

    public function resolve() {
      $this->_arguments = func_get_args();
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
