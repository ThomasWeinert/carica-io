<?php

namespace Carica\Io\Event {

  interface Loop {

    function add(Loop\Listener $listener);

    function remove(Loop\Listener $listener);

    function run();

    function stop();

  }

}