<?php

namespace Carica\Io\Event\Loop\React {

  use Carica\Io\Event\Loop\Listener;

  class Identifier implements Listener {

    public const TYPE_TIMEOUT = 1;
    public const TYPE_INTERVAL = 2;
    public const TYPE_STREAMREADER = 3;

    /**
     * @var int
     */
    private $_type;

    private $_data;

    public function __construct(int $type, $data) {
      $this->_type = $type;
      $this->_data = $data;
    }

    public function getType(): int {
      return $this->_type;
    }

    public function getData() {
      return $this->_data;
    }
  }
}
