<?php

namespace Carica\Io\Network\Http\Response {

  use Carica\Io\Network;

  /**
   *
   * @property-read string $type Mimetype
   * @property-read string $encoding Character encoding
   * @property-read integer $length Byte length
   */
  abstract class Content {

    private $_type = 'text/plain';
    private $_encoding = '';

    abstract public function sendTo(Network\Connection $connection);

    public function __construct($type = NULL, $encoding = NULL) {
      if (isset($type)) {
        $this->_type = $type;
      }
      if (isset($encoding)) {
        $this->_encoding = $encoding;
      }
    }

    public function __get($name) {
      switch ($name) {
      case 'type' :
      case 'encoding' :
        return $this->{'_'.$name};
      case 'length' :
        return $this->getLength();
      }
      throw new \LogicException(
        sprintf('Unknown property %s::$%s', get_class($this), $name)
      );
    }

    public function __set($name, $value) {
      switch ($name) {
      case 'type' :
      case 'encoding' :
      case 'length' :
        throw new \LogicException(
          sprintf('Can not write readonly property %s::$%s', get_class($this), $name)
        );
      }
      throw new \LogicException(
        sprintf('Unknown property %s::$%s', get_class($this), $name)
      );
    }

    public function getLength() {
      return 0;
    }
  }
}
