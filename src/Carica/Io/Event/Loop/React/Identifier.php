<?php

namespace Carica\Io\Event\Loop\React {

  class Identifier {

    const TYPE_TIMEOUT = 1;
    const TYPE_INTERVAL = 2;
    const TYPE_STREAMREADER = 3;

    private $_type = 0;
    private $_data = NULL;

    public function __construct($type, $data) {
      $this->_type = $type;
      $this->_data = $data;
    }

    public function getType() {
      return $this->_type;
    }

    public function getData() {
      return $this->_data;
    }
  }
}