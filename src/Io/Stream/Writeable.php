<?php

namespace Carica\Io\Stream {

  use Carica\Io;

  interface Writeable extends Io\Stream {

    function write($data);

  }

}