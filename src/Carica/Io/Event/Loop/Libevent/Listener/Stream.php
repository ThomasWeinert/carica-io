<?php

namespace Carica\Io\Event\Loop\Libevent\Listener {

  use Carica\Io;
  use Carica\Io\Event;
  use Carica\Io\Event\Loop\Libevent;

  class Stream extends Libevent\Listener {

    private $_read = NULL;
    private $_write = NULL;
    private $_stream = NULL;

    public function __construct(Event\Loop $loop, $stream) {
      parent::__construct($loop);
      $this->_read = new Io\Callbacks();
      $this->_write = new Io\Callbacks();
      $this->_stream = $stream;
    }

    public function __destruct() {
      var_dump('destruct buffer event', $this->_event);
      /**
      if (is_resource($this->_event)) {
        event_buffer_free($this->_event);
      }*/
    }

    public function onRead(Callable $callback) {
      $this->_read->add($result = new Stream\Callback($this, $this->_read->remove, $callback));
      $this->setUp();
      return $result;
    }

    public function onWrite(Callable $callback) {
      $this->_write->add($result = new Stream\Callback($this, $this->_write->remove, $callback));
      $this->setUp();
      return $result;
    }

    private function setUp() {
      $hasEvents = count($this->_read) + count($this->_write);
      if ($hasEvents > 0) {
        if (is_null($this->_event)) {
          $this->_event = $event = event_new();
          $that = $this;
          event_set(
            $this->_event,
            $this->_stream,
            EV_READ | EV_WRITE,
            function ($stream, $events) use ($event, $that) {
              if (($events & EV_READ) == EV_READ) {
                call_user_func($that->_read);
              }
              if (($events & EV_WRITE) == EV_WRITE) {
                call_user_func($that->_write);
              }
              event_add($this->_event, 1000000);
            }
          );
          event_base_set($this->_event, $this->getLoop()->getBase());
          event_add($this->_event, 0);
        }
      } else {
        $this->getLoop()->remove($this);
      }
    }

    public function getStream() {
      return $this->_stream;
    }
  }
}
