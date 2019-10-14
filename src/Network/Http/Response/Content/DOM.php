<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io\Network\Http\Response;
  use DOMDocument;

  /**
   * An response that's filled using a DOM document
   *
   * @property DOMDocument $document
   */
  abstract class DOM extends Response\Content {

    private $_document;

    public function __construct(string $type = 'application/xml; charset=utf-8') {
      parent::__construct($type);
      $this->_document = new DOMDocument('1.0', 'utf-8');
    }

    public function __get($name) {
      switch ($name) {
      case 'document' :
        return $this->_document;
      }
      return parent::__get($name);
    }
  }
}
