<?php
declare(strict_types=1);

namespace Carica\Io\Event\Emitter\Listener  {

  use Carica\Io\Event\Emitter as EventEmitter;
  use Carica\Io\Event\Emitter\Listener as EventEmitterListener;
  use LogicException;

  /**
   * @property EventEmitter $emitter
   * @property string $event
   * @property callable $callback
   */
  class On implements EventEmitterListener {

    /**
     * @var EventEmitter
     */
    private $_emitter;
    /**
     * @var string
     */
    private $_event;
    /**
     * @var callable
     */
    private $_callback;

    /**
     * @param EventEmitter $emitter
     * @param string $event
     * @param callable $callback
     */
    public function __construct(EventEmitter $emitter, string $event, callable $callback) {
      $this->_emitter = $emitter;
      $this->_event = $event;
      $this->_callback = $callback;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
      switch ($name) {
      case 'emitter' :
      case 'event' :
      case 'callback' :
        return TRUE;
      }
      return FALSE;
    }

    /**
     * @throws LogicException
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
      switch ($name) {
      case 'emitter' :
        return $this->_emitter;
      case 'event' :
        return $this->_event;
      case 'callback' :
        return $this->_callback;
      }
      throw new LogicException(sprintf('Property %s::$%s does not exists.', get_class($this), $name));
    }

    /**
     * @throws LogicException
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
      throw new LogicException(sprintf('%s is immutable.', get_class($this)));
    }


    /**
     * @throws LogicException
     * @param string $name
     */
    public function __unset($name) {
      throw new LogicException(sprintf('%s is immutable.', get_class($this)));
    }

    public function __invoke(...$arguments) {
      $callback = $this->_callback;
      $callback(...$arguments);
    }

    /**
     * @return callable
     */
    public function getCallback(): callable {
      return $this->_callback;
    }
  }
}
