<?php
declare(strict_types=1);

namespace Carica\Io\Event\Emitter {

  use Carica\Io\Event;
  use Carica\Io\Event\Emitter as EventEmitter;
  use InvalidArgumentException;
  use LogicException;
  use UnexpectedValueException;

  trait Aggregation {

    /**
     * @var EventEmitter
     */
    private $_eventEmitter;

    /**
     * Getter/Setter for the event emitter including implicit create.
     *
     * @param EventEmitter $emitter
     * @return EventEmitter
     */
    public function events(Event\Emitter $emitter = NULL): EventEmitter {
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
     * @return EventEmitter
     */
    protected function createEventEmitter(): EventEmitter {
      return new Event\Emitter();
    }

    /**
     * Avoid to create the emitter object just for emitting, without any callbacks attached
     *
     * @param string $event
     * @param array $arguments
     */
    protected function emitEvent(string $event, ...$arguments): void {
      if (isset($this->_eventEmitter) && !empty($event)) {
        $this->_eventEmitter->emit($event, ...$arguments);
      }
    }

    /**
     * A handler for dynamic method calls like $object->onEventName(). It can be used
     * implicit as __call() or called explicit. If called explicit, you can suppress
     * the exceptions and use your own error handling.
     *
     * @param string $method
     * @param array $arguments
     * @param bool $silent
     * @return bool
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    protected function callEmitter(string $method, array $arguments, bool $silent = FALSE): bool {
      $report = !$silent;
      $matches = array();
      if (preg_match('(^(?P<call>on|once)(?P<event>[A-Z].*))i', $method, $matches)) {
        try {
          if (
            !(
              isset($arguments) &&
              is_array($arguments) &&
              count($arguments) > 0 &&
              is_callable($arguments[0])
            )
          ) {
            throw new InvalidArgumentException(
              'No callable for event provided.'
            );
          }
          $arguments = $arguments ?? [ static function () {} ];
          $this->events()->{$matches['call']}($matches['event'], ...$arguments);
          return TRUE;
        } catch (UnexpectedValueException $e) {
          if ($report) {
            throw $e;
          }
        }
      } elseif ($report) {
        throw new LogicException(
          sprintf('Unknown method call %s::%s()', get_class($this), $method)
        );
      }
      return FALSE;
    }

    public function __call($method, $arguments) {
      return $this->callEmitter($method, $arguments);
    }
  }
}
