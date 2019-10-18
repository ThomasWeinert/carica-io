<?php
declare(strict_types=1);

namespace Carica\Io {

  use Carica\Io\Deferred\PromiseLike;

  /**
  * A deferred object implementation, allows to schedule callbacks for
  * execution after a condition is meet or not.
  *
  */
  class Deferred implements PromiseLike {

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
    private $_promise;

    /**
     * Callbacks if the object is resolved
     * .
     * @var Callable|Callbacks
     */
    private $_done;
    /**
     * Callbacks if the object is rejected
     * .
     * @var Callable|Callbacks
     */
    private $_failed;
    /**
     * Callbacks if the object is notified about a progress
     * .
     * @var Callable|Callbacks
     */
    private $_progress;

    /**
     * buffer for the arguments of the resolve/reject function,
     * used to execute functions, that are added after the object was finalized
     * .
     * @var array
     */
    private $_finishArguments = [];
    /**
     * Buffer for the last progress notification arguments, used
     * to bring new callback up to date.
     * .
     * @var NULL|array
     */
    private $_progressArguments;

    /**
     * Create object and initialize callback lists
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
     * @return PromiseLike
     */
    public function always(Callable $callback): PromiseLike {
      $this->_done->add($callback);
      $this->_failed->add($callback);
      $this->callIf(
        $this->_state !== self::STATE_PENDING,
        $callback,
        $this->_finishArguments
      );
      return $this;
    }


    /**
     * Add a callback that will be executed if the object is resolved
     *
     * @param Callable $callback
     * @return PromiseLike
     */
    public function done(Callable $callback): PromiseLike {
      $this->_done->add($callback);
      $this->callIf(
        $this->_state === self::STATE_RESOLVED,
        $callback,
        $this->_finishArguments
      );
      return $this;
    }

    /**
     * Add a callback that will be executed if the object was rejected
     *
     * @param Callable $callback
     * @return PromiseLike
     */
    public function fail(Callable $callback): PromiseLike {
      $this->_failed->add($callback);
      $this->callIf(
        $this->_state === self::STATE_REJECTED,
        $callback,
        $this->_finishArguments
      );
      return $this;
    }

    /**
     * Validate if the object was finalized using reject.
     *
     * @return bool
     */
    public function isRejected(): bool {
      return $this->_state === self::STATE_REJECTED;
    }

    /**
     * Validate if the object was finalized using resolve.
     *
     * @return bool
     */
    public function isResolved(): bool {
      return $this->_state === self::STATE_RESOLVED;
    }

    /**
     * @return bool
     */
    public function isPending(): bool {
      return $this->_state === self::STATE_PENDING;
    }

    /**
     * Notify the object about the progress
     * @param array $arguments
     * @return $this
     */
    public function notify(...$arguments): self {
      if ($this->_state === self::STATE_PENDING) {
        $this->_progressArguments = $arguments;
        $callback = $this->_progress;
        $callback(...$this->_progressArguments);
      }
      return $this;
    }

    /**
     * Add a callback that will be executed if the object is notified about progress
     *
     * @param Callable $callback
     * @return PromiseLike
     */
    public function progress(Callable $callback): PromiseLike {
      $this->_progress->add($callback);
      if (NULL !== $this->_progressArguments) {
        $callback(...$this->_progressArguments);
      }
      return $this;
    }

    /**
     * Creates and returns a promise attached to this object, a promise is used to
     * attach callbacks and validate the status. But has no methods to change the status.
     *
     * @return Deferred\Promise
     */
    public function promise(): Deferred\Promise {
      if (NULL === $this->_promise) {
        $this->_promise = new Deferred\Promise($this);
      }
      return $this->_promise;
    }

    /**
     * Finalize the object and set the status to rejected - the action has failed.
     * This will execute all callbacks attached with fail() or always()
     *
     * @param array $arguments
     * @return $this
     */
    public function reject(...$arguments): self {
      return $this->end(self::STATE_REJECTED, $this->_failed, $arguments);
    }

    /**
     * Finalize the object and set the status to rejected - the action was successful.
     * This will execute all callbacks attached with done() or always()
     *
     * @param array $arguments
     * @return $this
     */
    public function resolve(...$arguments): self {
      return $this->end(self::STATE_RESOLVED, $this->_done, $arguments);
    }

    /**
     * Reset state to pending
     * @return $this
     */
    public function restart(): self {
      if ($this->_state !== self::STATE_PENDING) {
        $this->_state = self::STATE_PENDING;
      }
      return $this;
    }

    /**
     * Finalize the object if it is pending.
     *
     * @param string $state
     * @param callable $callback
     * @param array $arguments
     * @return $this
     */
    private function end(string $state, callable $callback, array $arguments): self {
      if ($this->_state === self::STATE_PENDING) {
        $this->_finishArguments = $arguments;
        $this->_state = $state;
        $callback(...$this->_finishArguments);
      }
      return $this;
    }

    /**
     * Return the state string. Here are constants for each state, too.
     *
     * @return string
     */
    public function state(): string {
      return $this->_state;
    }

    /**
     * Filter and/or chain Deferred instances.
     *
     * @param Callable $doneFilter
     * @param Callable $failFilter
     * @param Callable $progressFilter
     * @return PromiseLike
     */
    public function then(
      Callable $doneFilter = NULL,
      Callable $failFilter = NULL,
      Callable $progressFilter = NULL
    ): PromiseLike {
      $defer = new Deferred();
      $this->done(
        function (...$arguments) use ($defer, $doneFilter) {
          $this->callFilter($doneFilter, array($defer, 'resolve'), $arguments);
        }
      );
      $this->fail(
        function (...$arguments) use ($defer, $failFilter){
          $this->callFilter($failFilter, array($defer, 'reject'), $arguments);
        }
      );
      $this->progress(
        function (...$arguments) use ($defer, $progressFilter){
          $this->callFilter($progressFilter, array($defer, 'notify'), $arguments);
        }
      );
      return $defer->promise();
    }

    /**
     * Execute the callback if the condition is true.
     *
     * @param boolean $condition
     * @param callable $callback
     * @param array $arguments
     * @return bool
     */
    private function callIf($condition, callable $callback, array $arguments): bool {
      if ($condition) {
        $callback(...$arguments);
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
    private function callFilter($filter, callable $callback, array $arguments): void {
      if ($filter) {
        $arguments = array($filter(...$arguments));
      }
      $callback(...$arguments);
    }

    /**
     * Static method to the create a new Deferred object.
     *
     * @return Deferred
     */
    public static function create(): Deferred {
      return new Deferred();
    }

    /**
     * Provides a way to execute callback functions based on one or more
     * objects, usually Deferred objects that represent asynchronous events.
     *
     * @param array $arguments
     * @return PromiseLike
     */
    public static function when(...$arguments): PromiseLike {
      $counter = count($arguments);
      if ($counter === 1) {
        $argument = $arguments[0];
        if ($argument instanceOf self) {
          return $argument->promise();
        }
        if ($argument instanceOf PromiseLike) {
          return $argument;
        }
        $defer = new Deferred();
        $defer->resolve($argument);
        return $defer->promise();
      }
      if ($counter > 0) {
        $master = new Deferred();
        $resolveArguments = array();
        foreach ($arguments as $index => $argument) {
          if ($argument instanceOf PromiseLike) {
            $argument
              ->done(
                static function(...$arguments) use ($master, $index, &$counter, &$resolveArguments) {
                  $resolveArguments[$index] = $arguments;
                  if (--$counter === 0) {
                    ksort($resolveArguments);
                    $master->resolve(...$resolveArguments);
                  }
                }
              )
              ->fail(
                static function(...$arguments) use ($master) {
                  $master->reject(...$arguments);
                }
              );
          } else {
            $resolveArguments[$index] = array($argument);
            if (--$counter === 0) {
              ksort($resolveArguments);
              $master->resolve(...$resolveArguments);
            }
          }
        }
        return $master->promise();
      }
      $defer = new Deferred();
      $defer->resolve();
      return $defer->promise();
    }
  }
}
