<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io\Network\Connection as NetworkConnection;
  use Carica\Io\Network\Http\Response;

  class Text extends Response\Content {

    private $_data;

    public function __construct($data, $type = 'text/plain; charset=utf-8') {
      parent::__construct($type);
      $this->_data = (string)$data;
    }

    public function sendTo(NetworkConnection $connection) {
      $connection->write($this->_data);
      return TRUE;
    }

    public function getLength(): int {
      return strlen($this->_data);
    }
  }
}
