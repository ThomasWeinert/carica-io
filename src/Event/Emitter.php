<?php
declare(strict_types=1);

namespace Carica\Io\Event {

  use LogicException;
  use UnexpectedValueException;

  class Emitter {

    private $_events = [];

    private $_eventNames;
    private $_eventAliases = [];

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
     *   $events = ['read', 'write']
     *
     * To define aliases use the event names as keys:
     *
     *   $events = ['read' => ['data', 'received'], 'write' => 'send']
     *
     * @param string[] $events
     * @throws LogicException
     */
    public function defineEvents(array $events): void {
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
          $aliases = [$value];
        } else {
          $event = $value;
          $aliases = [];
        }
        $eventLower = strtolower($event);
        $this->_eventNames[$eventLower] = TRUE;
        foreach ($aliases as $alias) {
          $aliasLower = strtolower($alias);
          if (isset($this->_eventNames[$aliasLower])) {
            throw new LogicException('Alias "'.$alias.'" is already defined as event.');
          }
          if (
            isset($this->_eventAliases[$aliasLower]) &&
            $this->_eventAliases[$aliasLower] === $eventLower
          ) {
            throw new LogicException(
              'Alias "'.$alias.'" is already defined for event "'.$event.'".'
            );
          }
          $this->_eventAliases[$aliasLower] = $eventLower;
        }
      }
    }

    /**
     * Add a listener object. If a callable is added, it is wrapped into a listener
     *
     * @param string $event
     * @param callable|Emitter\Listener $listener
     */
    public function on(string $event, $listener): void {
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
     * @param callable|Emitter\Listener $listener
     */
    public function once(string $event, $listener): void {
      $event = $this->getName($event);
      $listener = $listener instanceOf Emitter\Listener\Once
        ? $listener : new Emitter\Listener\Once($this, $event, $listener);
      $this->on($event, $listener);
    }

    /**
     * Remove the specified listener from the event
     *
     * @param string $event
     * @param callable|Emitter\Listener $listener
     */
    public function removeListener(string $event, $listener): void {
      $event = $this->getName($event);
      if (isset($this->_events[$event])) {
        foreach ($this->_events[$event] as $key => $eventListener) {
          /** @var Emitter\Listener $eventListener */
          if ($eventListener === $listener || $eventListener->getCallback() === $listener) {
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
    public function removeAllListeners(string $event): void {
      $event = $this->getName($event);
      $this->_events[$event] = [];
    }

    /**
     * Return an list of a listeners attached to the event
     *
     * @param string $event
     * @return callable[]|Emitter\Listener[]
     */
    public function listeners(string $event): array {
      $event = $this->getName($event);
      return $this->_events[$event] ?? [];
    }

    /**
     * Emit an event to all attached listeners
     *
     * @param string $event
     * @param mixed [$argument,...]
     */
    public function emit(string $event, ...$arguments): void {
      $event = $this->getName($event);
      if (isset($this->_events[$event])) {
        foreach ($this->_events[$event] as $listener) {
          $listener(...$arguments);
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
     * @throws UnexpectedValueException
     */
    private function getName(string $event): string {
      $eventLower = strtolower($event);
      $eventLower = $this->_eventAliases[$eventLower] ?? $eventLower;
      if (isset($this->_eventNames) && !isset($this->_eventNames[$eventLower])) {
        throw new UnexpectedValueException('Unknown event name: "'.$event.'"');
      }
      return $eventLower;
    }
  }
}
