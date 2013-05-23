<?php

namespace Carica\Io\Firmata\Rest {

  use Carica\Io\Network\Http;
  use Carica\Io\Firmata;

  class Pins {

    private $_board;

    public function __construct(Firmata\Board $board) {
      $this->_board = $board;
    }

    public function __invoke() {
      return call_user_func_array(array($this, 'handle'), func_get_args());
    }

    public function handle(Http\Request $request, array $parameters) {
      $response = $request->createResponse();
      $response->content = new Http\Response\Content\Xml;
      $dom = $response->content->document;
      $dom->appendChild($boardNode = $dom->createElement('board'));
      if ($this->_board->isActive()) {
        $boardNode->setAttribute('active', 'yes');
        $boardNode->setAttribute('firmata', (string)$board->version);
      } else {
        $boardNode->setAttribute('active', 'no');
      }
    }
  }
}