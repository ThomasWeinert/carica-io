<?php

namespace Carica\Io\Event {

  class Emitter {

    private $_events = array();

    /**
     * Add a listener object. If a callable is added, it is wrapped into a listener
     *
     * @param string $event
     * @param \Callable|Emitter\Listener $listener
     */
    public function on($event, $listener) {
      $listener = $listener instanceOf Emitter\Listener
        ? $listener : new Emitter\Listener\On($this, $event, $listener);
      $this->_events[$event][] = $listener;
      $this->emit('newListener', $listener);
    }

    /**
     * Add a listener that is removed after it's first call. If a callable is added, it is wrapped
     * into a listener
     *
     * @param string $event
     * @param \Callable|Emitter\Listener $listener
     */
    public function once($event, $listener) {
      $listener = $listener instanceOf Emitter\Listener\Once
        ? $listener : new Emitter\Listener\Once($this, $event, $listener);
      $this->on($event, $listener);
    }

    /**
     * Remode the specified listenr from the event
     *
     * @param string $event
     * @param \Callable|Emitter\Listener $listener
     */
    public function removeListener($event, $listener) {
      if (isset($this->_events[$event])) {
        foreach ($this->_events[$event] as $key => $eventListener) {
          /**
           * @var Emitter\Listener $eventListener
           */
          if ($eventListener === $listener || $eventListener->getCallback() == $listener) {
            unset($this->_events[$event][$key]);
          }
        }
      }
    }

    /**
     * Remove all listener of an event
     *
     * @param string $event
     */
    public function removeAllListeners($event) {
      $this->_events[$event] = array();
    }

    /**
     * Return an list of a listeners attached to the event
     *
     * @param string $event
     * @return array(\Callable|Emitter\Listener)
     */
    public function listeners($event) {
      return isset($this->_events[$event]) ? $this->_events[$event] : array();
    }

    /**
     * Emit an event to all attached listeners
     *
     * @param string $event
     * @param mixed [$argument,...]
     */
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
