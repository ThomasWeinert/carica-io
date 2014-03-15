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

    /**
     * A handler for dynamic method calls like $object->onEventName(). It can be used
     * implicit as __call() or called explicit it. If called explicit, you can suppress
     * the exceptions and use your own error handling.
     *
     * @param string $method
     * @param array $arguments
     * @param bool $silent
     * @return bool
     * @throws \LogicException
     * @throws \UnexpectedValueException
     */
    protected function callEmitter($method, $arguments, $silent = FALSE) {
      $report = !$silent;
      $matches = array();
      if (preg_match('(^(?P<call>on|once)(?P<event>[A-Z].*))i', $method, $matches)) {
        try {
          array_unshift($arguments, $matches['event']);
          call_user_func_array(
            array($this->events(), $matches['call']), $arguments
          );
          return TRUE;
        } catch (\UnexpectedValueException $e) {
          if ($report) {
            throw $e;
          }
        }
      } elseif ($report) {
        throw new \LogicException(
          sprintf('Unknown method call %s::%s()', get_class($this), $method)
        );
      }
      return FALSE;
    }
  }
}