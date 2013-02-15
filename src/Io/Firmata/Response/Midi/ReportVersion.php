<?php

namespace Carica\Io\Firmata\Response\Midi {

  use Carica\Io\Firmata;

  class ReportVersion extends Firmata\Response\Midi {

    private $_major = 0;
    private $_minor = 0;

    public function __construct(array $bytes) {
      parent::__construct($bytes);
      $this->_major = $bytes[1];
      $this->_minor = $bytes[2];
    }

    public function __get($name) {
      switch ($name) {
      case 'major' :
        return $this->_major;
      case 'minor' :
        return $this->_minor;
      }
      throw new \LogicException(sprintf('Unknown property %s::$%s', __CLASS__, $name));
    }
  }
}