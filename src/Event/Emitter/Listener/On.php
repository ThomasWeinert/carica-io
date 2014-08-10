<?php

namespace Carica\Io\Event\Emitter\Listener  {

  use Carica\Io\Event;

  /**
   * @property Event\Emitter $emitter
   * @property string $event
   * @property callable $callback
   */
  class On implements Event\Emitter\Listener {

    private $_emitter = NULL;
    private $_event = NULL;
    private $_callback = NULL;

    /**
     * @param Event\Emitter $emitter
     * @param string $event
     * @param callable $callback
     */
    public function __construct(Event\Emitter $emitter, $event, $callback) {
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
        return isset($this->{'_'.$name});
      }
      return FALSE;
    }

    /**
     * @throws \LogicException
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
      switch ($name) {
      case 'emitter' :
      case 'event' :
      case 'callback' :
        return $this->{'_'.$name};
      }
      throw new \LogicException(sprintf('Property %s::$%s does not exists.', get_class($this), $name));
    }

    /**
     * @throws \LogicException
     * @param string $name
     * @param mixed $value
     */
    public function __set($name, $value) {
      throw new \LogicException(sprintf('%s is immutable.', get_class($this)));
    }


    /**
     * @throws \LogicException
     * @param string $name
     */
    public function __unset($name) {
      throw new \LogicException(sprintf('%s is immutable.', get_class($this)));
    }

    public function __invoke() {
      call_user_func_array($this->_callback, func_get_args());
    }

    /**
     * @return callable|null
     */
    public function getCallback() {
      return $this->_callback;
    }
  }
}