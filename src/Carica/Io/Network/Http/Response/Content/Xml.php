<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  class Xml extends Response\Content {

    private $_type = 'application/xml';
    private $_document = NULL;

    public function __construct($type = 'application/xml') {
      parent::__construct($type);
      $this->_document = new \DOMDocument('1.0', 'utf-8');
    }

    public function __get($name) {
      switch ($name) {
      case 'document' :
        return $this->{'_'.$name};
      }
      return parent::__get($name);
    }

    public function sendTo(Network\Connection $connection) {
      $connection->write($this->_document->saveXml());
    }

    public function getLength() {
      return strlen($this->_document->saveXml());
    }
  }
}
