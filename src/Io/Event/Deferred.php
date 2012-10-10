<?php

namespace Carica\Io\Event {

  use Carica\Io;

  class Deferred {

    private $_done = NULL;
    private $_failed = NULL;
    private $_progress = NULL;

    public function __construct() {
      $this->_done = new Io\Callbacks();
      $this->_failed = new Io\Callbacks();
      $this->_progress = new Io\Callbacks();
    }

    public function done(Callable $callback) {
      $this->_done->add($callback);
      return $this;
    }

    public function failed(Callable $callback) {
      $this->_failed->add($callback);
      return $this;
    }

    public function progress(Callable $callback) {
      $this->_progress->add($callback);
      return $this;
    }

    public function always(Callable $callback) {
      $this->_done->add($callback);
      $this->_failed->add($callback);
      return $this;
    }

    public function resolve() {
      call_user_func_array($this->_done, func_get_args());
    }

    public function reject() {
      call_user_func_array($this->_failed, func_get_args());
    }

    public function notify() {
      call_user_func_array($this->_progress, func_get_args());
    }

    public function then($done, $failed, $progress) {

    }
  }

  function when() {
    $promises = func_get_args();

  }
}
