<?php

namespace Carica\Io\Event\Emitter {

  use Carica\Io\Event;

  trait Aggregation {

    /**
     * @var Event\Emitter
     */
    private $_eventEmitter = NULL;

    /**
     * Getter/Setter for the event emitter including implicit create.
     *
     * @param Event\Emitter $emitter
     * @return Event\Emitter
     */
    public function events(Event\Emitter $emitter = NULL) {
      if (NULL !== $emitter) {
        $this->_eventEmitter = $emitter;
      } elseif (NULL === $this->_eventEmitter) {
        $this->_eventEmitter = $this->createEventEmitter();
      }
      return $this->_eventEmitter;
    }

    /**
     * Lazy create for the event emitter, overload to restrict/define
     * the events
     *
     * @return Event\Emitter
     */
    protected function createEventEmitter() {
      return new Event\Emitter();
    }

    /**
     * Avoid to create the emitter object just for emitting, without any callbacks attached
     *
     * @param $event
     */
    protected function emitEvent($event) {
      if (isset($this->_eventEmitter) && !empty($event)) {
        call_user_func_array(array($this->_eventEmitter, 'emit'), func_get_args());
      }
    }
  }
}