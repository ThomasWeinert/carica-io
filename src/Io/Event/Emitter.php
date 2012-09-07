<?php

namespace Carica\Io\Event {

  class Emitter {

    private $_events = array();

    public function on($event, $listener) {
      $listener = $listener instanceOf Emitter\Listener\On
        ? $listener : new Emitter\Listener\On($this, $event, $listener);
      $this->_events[$event][] = $listener;
      $this->emit('newListener', $listener);
    }

    public function once($event, $listener) {
      $listener = $callback instanceOf Emitter\Listener\Once
        ? $listener : new EventEmitterListener($this, $event, $listener);
      $this->on($event, $listener);
    }

    public function removeListener($event, $listener) {
      if (isset($this->_events[$event])) {
        foreach ($this->_events[$event] as $key => $eventListener) {
          if ($eventListener === $listener || $eventListener->getCallback() == $listener) {
            unset($this->_events[$event][$key]);
          }
        }
      }
    }

    public function removeAllListeners($event) {
      $this->_events[$event] = array();
    }

    public function listeners($event) {
      return isset($this->_events[$event]) ? $this->_events[$event] : array();
    }

    public function emit($event) {
      $arguments = func_get_args();
      array_shift($arguments);
      if (isset($this->_events[$event])) {
        foreach ($this->_events[$event] as $listener) {
          call_user_func_array($listener, $arguments);
        }
      }
    }
  }
}
