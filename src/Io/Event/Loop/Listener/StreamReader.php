<?php

namespace Carica\Io\Event\Loop\Listener {

  use Carica\Io\Event;
  use Carica\Io\Stream;

  class StreamReader extends Event\Loop\Listener {

    private $_stream = NULL;

    public function __construct(Stream\Readable $stream) {
      $this->_stream = $stream;
    }

    public function tick() {
      return $this->_stream->read();
    }

    public function getResource() {
      return $this->_stream->resource();
    }
  }
}