<?php

namespace Carica\Io\Network\Http\Response {

  use Carica\Io\Network;

  abstract class Content {

    private $_type = 'text/plain';

    abstract public function sendTo(Network\Connection $connection);

    public function __construct($type = NULL) {
      if (isset($type)) {
        $this->_type = $type;
      }
    }

    public function __get($name) {
      switch ($name) {
      case 'type' :
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
