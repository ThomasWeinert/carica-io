<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Response\Content {

  use Carica\Io\Network\HTTP\Response;
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
      if ($name === 'document') {
        return $this->_document;
      }
      return parent::__get($name);
    }
  }
}
