<?php

namespace Carica\Io\Event {

  interface Loop {

    function setTimeout(Callable $callback, $milliseconds);

    function setInterval(Callable $callback, $milliseconds);

    function setStreamReader(Callable $callback, $stream);

    function remove($listener);

    function run();

    function stop();

  }

}