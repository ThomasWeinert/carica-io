<?php

namespace Carica\Io\Event {

  use Carica\Io;
  use Carica\Io\Event\Loop\Listener;

  interface Loop extends \Countable {

    public function setTimeout(Callable $callback, int $milliseconds): Listener;

    public function setInterval(Callable $callback, int $milliseconds): Listener;

    public function setStreamReader(Callable $callback, $stream): Listener;

    public function remove(Listener $listener): void;

    public function run(Io\Deferred\Promise $for = NULL): void;

    public function stop(): void;

  }

}
