<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io as Io;
  use React\EventLoop as ReactEventLoop;

  class React implements Io\Event\Loop {

    /**
     * @var ReactEventLoop\LoopInterface
     */
    private $_loop = NULL;

    public function loop(ReactEventLoop\LoopInterface $loop = NULL) {
      if (isset($loop)) {
        $this->_loop = $loop;
      } elseif (NULL === $this->_loop) {
        $this->_loop = ReactEventLoop\Factory::create();
      }
      return $this->_loop;
    }

    public function setTimeout(Callable $callback, $milliseconds) {
      $timer = $this->loop()->addTimer($milliseconds / 1000, $callback);
      return new React\Identifier(React\Identifier::TYPE_TIMEOUT, $timer);
    }

    public function setInterval(Callable $callback, $milliseconds) {
      $timer = $this->loop()->addPeriodicTimer($milliseconds / 1000, $callback);
      return new React\Identifier(React\Identifier::TYPE_INTERVAL, $timer);
    }

    public function setStreamReader(Callable $callback, $stream) {
      $this->loop()->addReadStream($stream, $callback);
      return new React\Identifier(React\Identifier::TYPE_STREAMREADER, $stream);
    }

    public function remove($listener) {
      if ($listener instanceOf React\Identifier) {
        switch ($listener->getType()) {
        case React\Identifier::TYPE_TIMEOUT :
        case React\Identifier::TYPE_INTERVAL :
          $this->loop()->cancelTimer($listener->getData());
          break;
        case React\Identifier::TYPE_STREAMREADER :
          $this->loop()->removeReadStream($listener->getData());
          break;
        default :
          throw new \LogicException('Unknwon listener identifier type');
        }
      } else {
        throw new \LogicException('Listener is not a valid identifer');
      }
    }

    public function run(Io\Deferred\Promise $for = NULL) {
      $loop = $this->loop();
      if (isset($for) &&
          $for->state() === Io\Deferred::STATE_PENDING) {
        $for->always(
          function () use ($loop) {
            $loop->stop();
          }
        );
      }
      $loop->run();
    }

    public function stop() {
      $this->loop()->stop();
    }

    public function count() {
      return -1;
    }
  }
}