<?php
declare(strict_types=1);

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event\Loop as EventLoop;

  trait Aggregation {

    /**
     * @var EventLoop
     */
    private $_eventLoop = NULL;

    /**
     * Getter/Setter for the event loop including implicit create. The create uses
     * the factory fetching a global instance of the loop by default.
     *
     * @param EventLoop $loop
     * @return EventLoop
     */
    public function loop(EventLoop $loop = NULL): EventLoop {
      if (NULL !== $loop) {
        $this->_eventLoop = $loop;
      } elseif (NULL === $this->_eventLoop) {
        $this->_eventLoop = Factory::get();
      }
      return $this->_eventLoop;
    }
  }
}
