<?php

namespace Carica\Io\Event {

  class Emitter {

    private $_events = array();

    private $_eventNames = NULL;
    private $_eventAliases = array();

    /**
     * Define possible event names and aliases.
     *
     * If no event names are provided, all are possible. The event names are case insensitive.
     *
     * You can define aliases for events, listeners added for the aliases will be added
     * for the event the aliases specified. You can call the method, multiple times, the
     * definition will be merged to allow inheritance.
     *
     * To define just events provide a list:
     *
     *   $events = array('read', 'write')
     *
     * To define events use the event names as keys:
     *
     *   $events = array('read' => array('data', 'received'), 'write' => 'send')
     *
     * @param array $events
     * @throws \LogicException
     */
    public function defineEvents(array $events) {
      if (!isset($this->_eventNames)) {
        $this->_eventNames = array_flip(array_keys($this->_events));
        $this->_eventNames['new-listener'] = TRUE;
      }
      foreach ($events as $key => $value) {
        if (is_array($value)) {
          $event = $key;
          $aliases = $value;
        } elseif (is_string($key) && is_string($value) && !empty($key)) {
          $event = $key;
          $aliases = array($value);
        } else {
          $event = $value;
          $aliases = array();
        }
        $eventLower = strtolower($event);
        $this->_eventNames[$eventLower] = TRUE;
        foreach ($aliases as $alias) {
          $aliasLower = strtolower($alias);
          if (isset($this->_eventNames[$aliasLower])) {
            throw new \LogicException('Alias "'.$alias.'" is already defined as event.');
          }
          if (isset($this->_eventAliases[$aliasLower]) &&
              $this->_eventAliases[$aliasLower] == $eventLower) {
            throw new \LogicException(
              'Alias "'.$alias.'" is already defined for event "'.$event.'".'
            );
          }
          $this->_eventNames[$aliasLower] = $eventLower;
        }
      }
    }

    /**
     * Add a listener object. If a callable is added, it is wrapped into a listener
     *
     * @param string $event
     * @param \Callable|Emitter\Listener $listener
     */
    public function on($event, $listener) {
      $event = $this->getName($event);
      $listener = $listener instanceOf Emitter\Listener
        ? $listener : new Emitter\Listener\On($this, $event, $listener);
      $this->_events[$event][] = $listener;
      $this->emit('new-listener', $listener);
    }

    /**
     * Add a listener that is removed after it's first call. If a callable is added, it is wrapped
     * into a listener
     *
     * @param string $event
     * @param \Callable|Emitter\Listener $listener
     */
    public function once($event, $listener) {
      $event = $this->getName($event);
      $listener = $listener instanceOf Emitter\Listener\Once
        ? $listener : new Emitter\Listener\Once($this, $event, $listener);
      $this->on($event, $listener);
    }

    /**
     * Remove the specified listener from the event
     *
     * @param string $event
     * @param \Callable|Emitter\Listener $listener
     */
    public function removeListener($event, $listener) {
      $event = $this->getName($event);
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
      $event = $this->getName($event);
      $this->_events[$event] = array();
    }

    /**
     * Return an list of a listeners attached to the event
     *
     * @param string $event
     * @return array(\Callable|Emitter\Listener)
     */
    public function listeners($event) {
      $event = $this->getName($event);
      return isset($this->_events[$event]) ? $this->_events[$event] : array();
    }

    /**
     * Emit an event to all attached listeners
     *
     * @param string $event
     * @param mixed [$argument,...]
     */
    public function emit($event) {
      $event = $this->getName($event);
      $arguments = func_get_args();
      array_shift($arguments);
      if (isset($this->_events[$event])) {
        foreach ($this->_events[$event] as $listener) {
          call_user_func_array($listener, $arguments);
        }
      }
    }

    /**
     * Mae the event name lowercase to provide case insensitivity, map it against
     * the aliases. Throw an exception if the events are defined and the event is
     * not in the list.
     *
     * @param string $event
     * @return string
     * @throws \UnexpectedValueException
     */
    private function getName($event) {
      $eventLower = strtolower($event);
      if (isset($this->_eventAliases[$eventLower])) {
        $eventLower = $this->_eventAliases[$eventLower];
      }
      if (isset($this->_eventNames) && !isset($this->_eventNames[$eventLower])) {
        throw new \UnexpectedValueException('Unknown event name: "'.$event.'"');
      }
      return $eventLower;
    }
  }
}
