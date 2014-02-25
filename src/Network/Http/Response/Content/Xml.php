<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io;
  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  /**
   * An xml response content
   *
   * @property \DOMDocument $document
   */
  class Xml extends Dom {

    public function __construct($type = 'application/xml; charset=utf-8') {
      parent::__construct($type);
    }

    public function sendTo(Network\Connection $connection) {
      $connection->write($this->document->saveXml());
      return TRUE;
    }

    public function getLength() {
      return strlen($this->document->saveXml());
    }
  }
}
