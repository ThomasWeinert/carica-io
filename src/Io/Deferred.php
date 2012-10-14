<?php

namespace Carica\Io {

  class Deferred {

    const STATE_PENDING = 'pending';
    const STATE_RESOLVED = 'resolved';
    const STATE_REJECTED = 'rejected';

    private $_state = self::STATE_PENDING;
    private $_promise = NULL;

    private $_done = NULL;
    private $_failed = NULL;
    private $_progress = NULL;

    private $_finishArguments = array();
    private $_progressArguments = NULL;

    public function __construct() {
      $this->_done = new Callbacks();
      $this->_failed = new Callbacks();
      $this->_progress = new Callbacks();
    }

    public function always(Callable $callback) {
      $this->_done->add($callback);
      $this->_failed->add($callback);
      if ($this->_state != self::STATE_PENDING) {
        call_user_func_array($callback, $this->_finishArguments);
      }
      return $this;
    }

    public function done(Callable $callback) {
      $this->_done->add($callback);
      if ($this->_state == self::STATE_RESOLVED) {
        call_user_func_array($callback, $this->_finishArguments);
      }
      return $this;
    }

    public function fail(Callable $callback) {
      $this->_failed->add($callback);
      if ($this->_state == self::STATE_REJECTED) {
        call_user_func_array($callback, $this->_finishArguments);
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
      if ($this->_state == self::STATE_PENDING) {
        $this->_progressArguments = func_get_args();
        call_user_func_array($this->_progress, $this->_progressArguments);
      }
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
      if (NULL !== $this->_progressArguments) {
        call_user_func_array($callback, $this->_progressArguments);
      }
      return $this;
    }

    public function promise() {
      if (NULL === $this->_promise) {
        $this->_promise = new Deferred\Promise($this);
      }
      return $this->_promise;
    }

    public function reject() {
      if ($this->_state == self::STATE_PENDING) {
        $this->_finishArguments = func_get_args();
        $this->_state = self::STATE_REJECTED;
        call_user_func_array($this->_failed, $this->_finishArguments);
      }
    }

    public function resolve() {
      if ($this->_state == self::STATE_PENDING) {
        $this->_finishArguments = func_get_args();
        $this->_state = self::STATE_RESOLVED;
        call_user_func_array($this->_done, $this->_finishArguments);
      }
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
      } elseif (is_array($callbacks)) {
        foreach ($callbacks as $callback) {
          $add($callbacks);
        }
      }
    }

    public static function create() {
      new Deferred();
    }

    public static function when() {
      $arguments = func_get_args();
      $counter = count($arguments);
      if ($counter == 1) {
        $argument = $arguments[0];
        if ($argument instanceOf Deferred) {
          return $argument->promise();
        } elseif ($argument instanceOf Deferred\Promise) {
          return $argument;
        } else {
          $defer = new Deferred();
          $defer->resolve($argument);
          return $defer->promise();
        }
      } elseif ($counter > 0) {
        $master = new Deferred();
        $resolveArguments = array();
        foreach ($arguments as $index => $argument) {
          if ($argument instanceOf Deferred || $argument instanceOf Deferred\Promise) {
            $argument
              ->done(
                function() use ($master, $index, &$counter, &$resolveArguments) {
                  $resolveArguments[$index] = func_get_args();
                  if (--$counter == 0) {
                    ksort($resolveArguments);
                    call_user_func_array(array($master, 'resolve'), $resolveArguments);
                  }
                }
              )
              ->fail(
                function() use ($master) {
                  $master->fail();
                }
              );
          } else {
            $resolveArguments[$index] = array($argument);
            if (--$counter == 0) {
              ksort($resolveArguments);
              call_user_func_array(array($master, 'resolve'), $resolveArguments);
            }
          }
        }
        return $master->promise();
      } else {
        $master = new Deferred();
        $defer->resolve();
        return $defer->promise();
      }
    }
  }
}
