<?php

namespace Carica\Io\Event\Loop\Libevent\Listener {

  use Carica\Io;
  use Carica\Io\Event;
  use Carica\Io\Event\Loop\Libevent;

  class Stream extends Libevent\Listener {

    private $_read = NULL;
    private $_write = NULL;
    private $_stream = NULL;

    public function __construct(Event\Loop\Libevent $loop, $stream) {
      $that = $this;
      parent::__construct(
        $loop,
        function ($events) use ($that) {
          if (($events & EV_READ) == EV_READ && $that->_read) {
            call_user_func($that->_read);
          }
          if (($events & EV_WRITE) == EV_WRITE && $that->_write) {
            call_user_func($that->_write);
          }
        }
      );
      $this->_stream = $stream;
    }

    public function onRead(Callable $callback) {
      if (is_null($this->_read)) {
        $this->_read = new Io\Callbacks();
      }
      $this->_read->add($result = new Stream\Callback($this, $this->_read->remove, $callback));
      $this->update();
      return $result;
    }

    public function onWrite(Callable $callback) {
      if (is_null($this->_write)) {
        $this->_write = new Io\Callbacks();
      }
      $this->_write->add($result = new Stream\Callback($this, $this->_write->remove, $callback));
      $this->update();
      return $result;
    }

    public function update() {
      $hasEvents = FALSE;
      if ($this->_read && count($this->_read)) {
        $hasEvents = TRUE;
      }
      if ($this->_write && count($this->_write)) {
        $hasEvents = TRUE;
      }
      if ($hasEvents) {
        if (is_null($this->_event)) {
          $this->_event = $event = event_new();
          $that = $this;
          $callback = function ($stream, $events) use ($event, $that, &$callback) {
            if (!$that->isCancelled()) {
              call_user_func($this->getCallback(), $events);
              if (is_resource($that->getStream()) && is_resource($event)) {
                event_add($event, 1000000);
              }
            }
          };
          event_set(
            $this->_event,
            $this->_stream,
            EV_READ | EV_WRITE,
            $callback
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

    public function free() {
      $this->_read = NULL;
      $this->_write = NULL;
      $this->_stream = NULL;
      parent::free();
    }
  }
}
