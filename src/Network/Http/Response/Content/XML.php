<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io\Network;

  /**
   * An xml response content
   */
  class XML extends DOM {

    public function __construct($type = 'application/xml; charset=utf-8') {
      parent::__construct($type);
    }

    public function sendTo(Network\Connection $connection) {
      $connection->write($this->document->saveXml());
      return TRUE;
    }

    public function getLength(): int {
      return strlen($this->document->saveXml());
    }
  }
}
