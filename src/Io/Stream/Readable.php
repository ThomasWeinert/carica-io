<?php

namespace Carica\Io\Stream {

  use Carica\Io;

  interface Readable extends Io\Stream {

    function read($bytes = 1024);

  }

}