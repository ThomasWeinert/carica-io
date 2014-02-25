<?php

namespace Carica\Io\Event {

  use Carica\Io;

  interface Loop extends \Countable {

    function setTimeout(Callable $callback, $milliseconds);

    function setInterval(Callable $callback, $milliseconds);

    function setStreamReader(Callable $callback, $stream);

    function remove($listener);

    function run(Io\Deferred\Promise $for = NULL);

    function stop();

  }

}