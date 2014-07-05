<?php

namespace Carica\Io {

  /**
  * A deferred object implementation, allows to schedule callbacks for
  * execution after a condition is meet or not.
  *
  */
  class Deferred {

    /**
     * Default state - not yet finalized
     * @var string
     */
    const STATE_PENDING = 'pending';
    /**
     * Final state, object was resolved, the action was successful
     * @var string
     */
    const STATE_RESOLVED = 'resolved';

    /**
     * Final state, object was rejected, the action failed
     *
     * @var string
     */
    const STATE_REJECTED = 'rejected';

    /**
     * current state
     * .
     * @var string
     */
    private $_state = self::STATE_PENDING;
    /**
     * An promise for this object
     * .
     * @var Deferred\Promise
     */
    private $_promise = NULL;

    /**
     * Callbacks if the object is resolved
     * .
     * @var Callable|Callbacks
     */
    private $_done = NULL;
    /**
     * Callbacks if the object is rejected
     * .
     * @var Callable|Callbacks
     */
    private $_failed = NULL;
    /**
     * Callbacks if the object is notified about a progress
     * .
     * @var Callable|Callbacks
     */
    private $_progress = NULL;

    /**
     * buffer for the arguments of the resolve/reject function,
     * used to execute functions, that are added after the object was finalized
     * .
     * @var array
     */
    private $_finishArguments = array();
    /**
     * Buffer for the last progress notification arguments, used
     * to bring new callback up to date.
     * .
     * @var NULL|array
     */
    private $_progressArguments = NULL;

    /**
     * Create object and intialize callback lists
     */
    public function __construct() {
      $this->_done = new Callbacks();
      $this->_failed = new Callbacks();
      $this->_progress = new Callbacks();
    }

    /**
     * Add a callback that will be execute if the object is finalized with
     * resolved or reject
     *
     * @param Callable $callback
     * @return Deferred
     */
    public function always(Callable $callback) {
      $this->_done->add($callback);
      $this->_failed->add($callback);
      $this->callIf(
        $this->_state != self::STATE_PENDING,
        $callback,
        $this->_finishArguments
      );
      return $this;
    }

    /**
     * Add a callback that will be executed if the object is resolved
     *
     * @param Callable $callback
     * @return Deferred
     */
    public function done(Callable $callback) {
      $this->_done->add($callback);
      $this->callIf(
        $this->_state == self::STATE_RESOLVED,
        $callback,
        $this->_finishArguments
      );
      return $this;
    }

    /**
     * Add a callback that will be eecuted if the object was rejected
     *
     * @param Callable $callback
     * @return Deferred
     */
    public function fail(Callable $callback) {
      $this->_failed->add($callback);
      $this->callIf(
        $this->_state == self::STATE_REJECTED,
        $callback,
        $this->_finishArguments
      );
      return $this;
    }

    /**
     * Validate if the object was finilized using reject.
     *
     * @return string
     */
    public function isRejected() {
      return $this->_state == self::STATE_REJECTED;
    }

    /**
     * Validate if the object was finilized using resolve.
     *
     * @return string
     */
    public function isResolved() {
      return $this->_state == self::STATE_RESOLVED;
    }

    /**
     * Notify the object about the progress
     */
    public function notify() {
      if ($this->_state == self::STATE_PENDING) {
        $this->_progressArguments = func_get_args();
        call_user_func_array($this->_progress, $this->_progressArguments);
      }
      return $this;
    }

    /**
     * Utility method to filter and/or chain Deferreds.
     *
     * @param Callable $doneFilter
     * @param Callable $failFilter
     * @param Callable $progressFilter
     * @return Deferred\Promise
     */
    public function pipe(
      Callable $doneFilter = NULL,
      Callable $failFilter = NULL,
      Callable $progressFilter = NULL
    ) {
      $defer = new Deferred();
      $this->done(
        function () use ($defer, $doneFilter) {
          $this->callFilter($doneFilter, array($defer, 'resolve'), func_get_args());
        }
      );
      $this->fail(
        function () use ($defer, $failFilter){
          $this->callFilter($failFilter, array($defer, 'reject'), func_get_args());
        }
      );
      $this->progress(
        function () use ($defer, $progressFilter){
          $this->callFilter($progressFilter, array($defer, 'notify'), func_get_args());
        }
      );
      return $defer->promise();
    }

    /**
     * Add a callback that will be executed if the object is notified about progress
     *
     * @param Callable $callback
     * @return Deferred
     */
    public function progress(Callable $callback) {
      $this->_progress->add($callback);
      if (NULL !== $this->_progressArguments) {
        call_user_func_array($callback, $this->_progressArguments);
      }
      return $this;
    }

    /**
     * Creates and returns a promise attached to this object, a promise is used to
     * attach callbacks and validate the status. But has no methods to change the status.
     *
     * @return Deferred\Promise
     */
    public function promise() {
      if (NULL === $this->_promise) {
        $this->_promise = new Deferred\Promise($this);
      }
      return $this->_promise;
    }

    /**
     * Finalize the object and set the status to rejected - the action has failed.
     * This will execute all callbacks attached with fail() or always()
     *
     * @return Deferred
     */
    public function reject() {
      return $this->end(self::STATE_REJECTED, $this->_failed, func_get_args());
    }

    /**
     * Finalize the object and set the status to rejected - the action was successful.
     * This will execute all callbacks attached with done() or always()
     *
     * @return Deferred
     */
    public function resolve() {
      return $this->end(self::STATE_RESOLVED, $this->_done, func_get_args());
    }

    /**
     * Finalize the object if it is pending.
     *
     * @param string $state
     * @param callable $callback
     * @param array $arguments ;
     * @return $this
     */
    private function end($state, callable $callback, array $arguments)
    {
      if ($this->_state == self::STATE_PENDING) {
        $this->_finishArguments = $arguments;
        $this->_state = $state;
        call_user_func_array($callback, $this->_finishArguments);
      }
      return $this;
    }

    /**
     * Return the state string. Here are constants for each state, too.
     *
     * @return string
     */
    public function state() {
      return $this->_state;
    }

    /**
     * Add handlers to be called when the Deferred object is resolved
     * or rejected or notified about progress. Basically a shortcut for
     * done(), fail() and progress().
     *
     * @param Callable|array(Callable) $done
     * @param Callable|array(Callable) $fail
     * @param Callable|array(Callable) $progress
     * @return Deferred
     */
    public function then($done = NULL, $fail = NULL, $progress = NULL) {
      $this->addCallbacksIfProvided(array($this, 'done'), $done);
      $this->addCallbacksIfProvided(array($this, 'fail'), $fail);
      $this->addCallbacksIfProvided(array($this, 'progress'), $progress);
      return $this;
    }

    /**
     * Check if $callbacks is a single callback or an array of callbacks.
     * The $add parameter is the method used to add the actual callbacks to the
     * deferred object.
     *
     * @param Callable $add
     * @param Callable|array(Callable) $callbacks
     */
    private function addCallbacksIfProvided($add, $callbacks) {
      if (is_callable($callbacks)) {
        $add($callbacks);
      } elseif (is_array($callbacks)) {
        foreach ($callbacks as $callback) {
          $add($callback);
        }
      }
    }

    /**
     * Execute the callback if the condition is true.
     *
     * @param boolean $condition
     * @param callable $callback
     * @param array $arguments
     * @return bool
     */
    private function callIf($condition, callable $callback, array $arguments)
    {
      if ($condition) {
        call_user_func_array($callback, $arguments);
        return TRUE;
      }
      return FALSE;
    }

    /**
     * Execute callback after using filter if defined.
     *
     * @param callable|NULL $filter
     * @param callable $callback
     * @param array $arguments
     */
    private function callFilter($filter, callable $callback, array $arguments)
    {
      if ($filter) {
        $arguments = array(call_user_func_array($filter, $arguments));
      }
      call_user_func_array($callback, $arguments);
    }

    /**
     * Static method to the create a new Deferred object.
     *
     * @return Deferred
     */
    public static function create() {
      return new Deferred();
    }

    /**
     * Provides a way to execute callback functions based on one or more
     * objects, usually Deferred objects that represent asynchronous events.
     *
     * @return Deferred\Promise
     */
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
          /**
           * @var Deferred\Promise $argument
           */
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
                  call_user_func_array(array($master, 'reject'), func_get_args());
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
        $defer = new Deferred();
        $defer->resolve();
        return $defer->promise();
      }
    }
  }
}
