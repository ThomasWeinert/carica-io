<?php

namespace Carica\Io\Firmata\Response {

  use Carica\Io\Firmata;

  class Midi extends Firmata\Response {

    protected $_command = 0x00;

    public function command() {
      return $this->_command;
    }

  }
}