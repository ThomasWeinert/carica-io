<?php
declare(strict_types=1);

namespace Carica\Io\Event {

  use Carica\Io\Deferred\PromiseLike;
  use Carica\Io\Event\Loop\Listener as EventLoopListener;

  interface Loop {

    public function setTimeout(Callable $callback, int $milliseconds): EventLoopListener;

    public function setInterval(Callable $callback, int $milliseconds): EventLoopListener;

    public function setStreamReader(Callable $callback, $stream): EventLoopListener;

    public function remove(EventLoopListener $listener): void;

    public function run(PromiseLike $for = NULL): void;

    public function stop(): void;

    public static function create(): self;

    /**
     * Return the global event loop instance stored in the
     * loop factory.
     *
     * @return static
     */
    public static function get(): self;
  }
}
