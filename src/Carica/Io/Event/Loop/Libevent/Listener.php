<?php

namespace Carica\Io\Event\Loop\Libevent {

  use Carica\Io\Event;

  abstract class Listener {

    private $_loop = NULL;
    protected $_event = NULL;

    public function __construct(Event\Loop\Libevent $loop) {
      $this->_loop = $loop;
    }

    public function __destruct() {
      $this->free();
      if (is_resource($this->_event)) {
        event_free($this->_event);
      }
    }

    public function getEvent() {
      return $this->_event;
    }

    public function getLoop() {
      return $this->_loop;
    }

    public function free() {
      if (is_resource($this->_event)) {
        event_del($this->_event);
      }
    }
  }
}