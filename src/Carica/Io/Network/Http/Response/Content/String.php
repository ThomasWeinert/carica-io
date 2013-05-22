<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  class String extends Response\Content {

    private $_type = 'text/plain';
    private $_data = '';

    public function __construct($data, $type = 'text/plain') {
      $this->_data = $data;
      $this->_type = $type;
    }

    public function sendTo(Network\Connection $connection) {
      $connection->write($this->_data);
    }

    public function getLength() {
      return strlen($this->_data);
    }
  }
}
