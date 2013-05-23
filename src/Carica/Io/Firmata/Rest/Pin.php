<?php

namespace Carica\Io\Firmata\Rest {

  use Carica\Io\Network\Http;
  use Carica\Io\Firmata;

  class Pin {

    private $_board;

    private $_modeStrings = array(
      Firmata\PIN_STATE_INPUT => 'input',
      Firmata\PIN_STATE_OUTPUT => 'output',
      Firmata\PIN_STATE_ANALOG => 'analog',
      Firmata\PIN_STATE_PWM => 'pwm',
      Firmata\PIN_STATE_SERVO => 'servo'
    );

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
        $this->appendPin($boardNode, (int)$pameters['pin']);
      } else {
        $boardNode->setAttribute('active', 'no');
      }
      return $response;
    }

    public function appendPin(\DOMElement $parent, $pinId) {
      if (isset($this->_board->pins[$pinId])) {
        $dom = $parent->ownerDocument;
        $pin = $this->_board->pins[$pinId];
        $parent->appendChild($pinNode = $dom->createElement('pin'));
        $pinNode->setAttribute('mode', $this->getModeString($pin->mode));
        switch ($pin->mode) {
        case Firmata\PIN_STATE_INPUT :
        case Firmata\PIN_STATE_OUTPUT :
          $pinNode->setAttribute('digital', $pin->digital ? 'yes' : 'no');
          break;
        case Firmata\PIN_STATE_ANALOG :
        case Firmata\PIN_STATE_PWM :
        case Firmata\PIN_STATE_SERVO :
          $pinNode->setAttribute('analog', $pin->analog);
          break;
        }
      }
    }

    private function getModeString($mode) {
      return isset($this->_modeStrings[$mode]) ? $this->_modeStrings[$mode] : 'unknown';
    }
  }
}