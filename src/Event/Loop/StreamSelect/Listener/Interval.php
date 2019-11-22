<?php
declare(strict_types=1);

namespace Carica\Io\Event\Loop\StreamSelect\Listener {

  use Carica\Io\Event;
  use Carica\Io\Event\Loop\StreamSelect;

  class Interval extends StreamSelect\Listener {

    private $_interval;
    private $_next;

    /**
     * @var callable
     */
    private $_callback;

    public function __construct(Event\Loop $loop, Callable $callback, int $milliseconds) {
      parent::__construct($loop);
      $this->_interval = (int)$milliseconds;
      $this->_callback = $callback;
      $this->_next = $this->getNow() + $milliseconds;
    }

    public function tick(): bool {
      $now = $this->getNow();
      if ($now >= $this->_next) {
        $this->reset();
        ($this->_callback)();
        return TRUE;
      }
      return FALSE;
    }

    public function reset(): void {
      $this->_next = $this->getNow() + $this->_interval;
    }

    private function getNow(): int {
      $loop = $this->getLoop();
      if ($loop instanceOf Event\Loop\Clock) {
        return $loop->getNow();
      }
      return (int)ceil(microtime(TRUE) * 1000);
    }
  }
}
