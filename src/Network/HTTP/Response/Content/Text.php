<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Response\Content {

  use Carica\Io\Network\Connection as NetworkConnection;
  use Carica\Io\Network\HTTP\Response;

  class Text extends Response\Content {

    private $_data;

    public function __construct(string $data, string $type = 'text/plain; charset=utf-8') {
      parent::__construct($type);
      $this->_data = $data;
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
