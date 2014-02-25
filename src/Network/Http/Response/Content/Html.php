<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io;
  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  class Html extends Dom {

    public function __construct($type = 'text/html; charset=utf-8') {
      parent::__construct($type);
    }

    public function sendTo(Network\Connection $connection) {
      $connection->write($this->document->saveHtml());
      return TRUE;
    }

    public function getLength() {
      return strlen($this->document->saveXml());
    }
  }
}
