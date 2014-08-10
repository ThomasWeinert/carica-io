<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io as Io;
  use Alert as Alert;

  class AlertReactor implements Io\Event\Loop {

    /**
     * @var Alert\Reactor
     */
    private $_reactor = NULL;

    public function reactor(Alert\Reactor $reactor = NULL)
    {
      if (isset($reactor)) {
        $this->_reactor = $reactor;
      } elseif (NULL === $this->_reactor) {
        $this->_reactor = (new Alert\ReactorFactory)->select();;
      }
      return $this->_reactor;
    }

    public function setTimeout(Callable $callback, $milliseconds) {
      return $this->reactor()->once($callback, $milliseconds / 1000);
    }

    public function setInterval(Callable $callback, $milliseconds) {
      return $this->reactor()->repeat($callback, $milliseconds / 1000);
    }

    public function setStreamReader(Callable $callback, $stream) {
      return $this->reactor()->onReadable($stream, $callback, TRUE);
    }

    public function remove($watcherId) {
      $this->reactor()->cancel($watcherId);
    }

    public function run(Io\Deferred\Promise $for = NULL) {
      $reactor = $this->reactor();
      if (isset($for) &&
          $for->state() === Io\Deferred::STATE_PENDING) {
        $for->always(
          function () use ($reactor) {
            $reactor->stop();
          }
        );
      }
      $reactor->run();
    }

    public function stop() {
      $this->reactor()->stop();
    }

    public function count() {
      return -1;
    }
  }
}