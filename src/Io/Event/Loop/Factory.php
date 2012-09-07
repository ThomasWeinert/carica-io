<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event;

  class Factory {

    public static function create() {
      return new StreamSelect();
    }
  }
}