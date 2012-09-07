<?php

namespace Carica\Io\Event\Emitter\Listener {

  use Carica\Io\Event;

  class On {

    protected $_emitter = NULL;
    protected $_event = NULL;
    protected $_callback = NULL;

    public function __construct(Event\Emitter $emitter, $event, $callback) {
      $this->_emitter = $emitter;
      $this->_event = $event;
      $this->_callback = $callback;
    }

    public function __invoke() {
      call_user_func_array($this->_callback, func_get_args());
    }

    public function getCallback() {
      return $this->_callback;
    }
  }
}