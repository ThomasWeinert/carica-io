<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io;
  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  /**
   * An response that's filled using a DOM document
   *
   * @property \DOMDocument $document
   */
  abstract class Dom extends Response\Content {

    private $_document = NULL;

    public function __construct($type = 'application/xml; charset=utf-8') {
      parent::__construct($type);
      $this->_document = new \DOMDocument('1.0', 'utf-8');
    }

    public function __get($name) {
      switch ($name) {
      case 'document' :
        return $this->{'_'.$name};
      }
      /** @noinspection PhpVoidFunctionResultUsedInspection */
      return parent::__get($name);
    }
  }
}
