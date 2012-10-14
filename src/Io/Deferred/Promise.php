<?php

namespace Carica\Io\Deferred {

  use Carica\Io;

  class Promise {

    private $_defer = NULL;

    public function __construct(Io\Deferred $defer) {
      $this->_defer = $defer;
    }

    public function always(Callable $callback) {
      $this->_defer->always($callback);
      return $this;
    }

    public function done(Callable $callback) {
      $this->_defer->done($callback);
      return $this;
    }

    public function fail(Callable $callback) {
      $this->_defer->fail($callback);
      return $this;
    }

    public function pipe(
      Callable $doneFilter = NULL,
      Callable $failFilter = NULL,
      Callable $progressFilter = NULL
    ) {
      return $defer->pipe($doneFilter, $failFilter, $progressFilter);
    }

    public function progress(Callable $callback) {
      $this->_defer->progress($callback);
      return $this;
    }

    public function state() {
      return $this->_defer->state();
    }

    public function then($done = NULL, $fail = NULL, $progress = NULL) {
      $this->_defer->then($done, $fail, $progress);
      return $this;
    }
  }
}