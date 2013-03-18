<?php

namespace Carica\Io\Event\Emitter\Listener  {

  use Carica\Io\Event;

  class On implements Event\Emitter\Listener {

    private $_emitter = NULL;
    private $_event = NULL;
    private $_callback = NULL;

    public function __construct(Event\Emitter $emitter, $event, $callback) {
      $this->_emitter = $emitter;
      $this->_event = $event;
      $this->_callback = $callback;
    }

    public function __isset($name) {
      switch ($name) {
      case 'emitter' :
      case 'event' :
      case 'callback' :
        return isset($this->{'_'.$name});
      }
      return FALSE;
    }

    public function __get($name) {
      switch ($name) {
      case 'emitter' :
      case 'event' :
      case 'callback' :
        return $this->{'_'.$name};
      }
      throw new \LogicError(sprintf('Property %s::$%s does not exists.', get_class($this), $name));
    }

    public function __set($name, $value) {
      throw new \LogicError(sprintf('%s is immutable.', get_class($this)));
    }

    public function __unset($name) {
      throw new \LogicError(sprintf('%s is immutable.', get_class($this)));
    }

    public function __invoke() {
      call_user_func_array($this->_callback, func_get_args());
    }

    public function getCallback() {
      return $this->callback;
    }
  }
}