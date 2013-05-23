<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  class String extends Response\Content {

    private $_data = '';

    public function __construct($data, $type = 'text/plain') {
      parent::__construct($type);
      $this->_data = (string)$data;
    }

    public function sendTo(Network\Connection $connection) {
      $connection->write($this->_data);
    }

    public function getLength() {
      return strlen($this->_data);
    }
  }
}
