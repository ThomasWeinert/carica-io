<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io\Network\Connection as NetworkConnection;

  /**
   * An xml response content
   */
  class XML extends DOM {

    public function __construct(string $type = 'application/xml; charset=utf-8') {
      parent::__construct($type);
    }

    public function sendTo(NetworkConnection $connection) {
      $connection->write($this->document->saveXml());
      return TRUE;
    }

    public function getLength(): int {
      return strlen($this->document->saveXml());
    }
  }
}
