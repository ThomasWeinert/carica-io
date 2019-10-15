<?php
declare(strict_types=1);

namespace Carica\Io\Deferred {

  use Carica\Io\Deferred;

  class Promise implements PromiseLike {

    protected $_defer;

    /**
     * Create the promise for a Deferred object.
     *
     * @param Deferred $defer
     */
    public function __construct(Deferred $defer) {
      $this->_defer = $defer;
    }

    /**
     * Add a callback that will be execute if the object is finalized with
     * resolved or reject
     *
     * @param callable $callback
     * @return PromiseLike
     */
    public function always(callable $callback): PromiseLike {
      $this->_defer->always($callback);
      return $this;
    }

    /**
     * Add a callback that will be executed if the object is resolved
     *
     * @param callable $callback
     * @return PromiseLike
     */
    public function done(callable $callback): PromiseLike {
      $this->_defer->done($callback);
      return $this;
    }

    /**
     * Add a callback that will be executed if the object was rejected
     *
     * @param callable $callback
     * @return PromiseLike
     */
    public function fail(callable $callback): PromiseLike {
      $this->_defer->fail($callback);
      return $this;
    }

    /**
     * Add a callback that will be executed if the object is notified about progress
     *
     * @param callable $callback
     * @return PromiseLike
     */
    public function progress(callable $callback): PromiseLike {
      $this->_defer->progress($callback);
      return $this;
    }

    /**
     * Return the state string. Here are constants for each state, too.
     *
     * @return string
     */
    public function state(): string {
      return $this->_defer->state();
    }

    /**
     * Filter and/or chain Deferred instances.
     *
     * @param callable $doneFilter
     * @param callable $failFilter
     * @param callable $progressFilter
     * @return PromiseLike
     */
    public function then(
      callable $doneFilter = NULL,
      callable $failFilter = NULL,
      callable $progressFilter = NULL
    ): PromiseLike {
      return $this->_defer->then($doneFilter, $failFilter, $progressFilter);
    }
  }
}
