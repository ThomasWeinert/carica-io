<?php

namespace Carica\Io\Event\Loop\Libevent {

  use Carica\Io\Event;

  abstract class Listener implements Event\Loop\Listener {

    /**
     * @var Event\Loop\Libevent
     */
    private $_loop;
    /**
     * @var bool
     */
    private $_isCancelled = FALSE;
    /**
     * @var callable
     */
    private $_callback;
    /**
     * @var NULL|resource
     */
    protected $_event;

    public function __construct(Event\Loop\Libevent $loop, Callable $callback) {
      $this->_loop = $loop;
      $this->_callback = $callback;
    }

    public function getEvent() {
      return $this->_event;
    }

    public function getLoop() {
      return $this->_loop;
    }

    public function getCallback() {
      return $this->_callback;
    }

    public function isCancelled() {
      return $this->_isCancelled;
    }

    public function cancel() {
      $this->_isCancelled = TRUE;
      if (is_resource($this->_event)) {
        event_del($this->_event);
        event_free($this->_event);
      }
    }
  }
}
