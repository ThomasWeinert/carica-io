<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Response\Content {

  use Carica\Io\Network;

  class HTML extends DOM {

    public function __construct(string $type = 'text/html; charset=utf-8') {
      parent::__construct($type);
    }

    public function sendTo(Network\Connection $connection) {
      $connection->write($this->document->saveHtml());
      return TRUE;
    }

    public function getLength(): int {
      return strlen($this->document->saveXml());
    }
  }
}
