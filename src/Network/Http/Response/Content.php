<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Response {

  use Carica\Io\Deferred\PromiseLike;
  use Carica\Io\Network\Connection as NetworkConnection;

  /**
   *
   * @property-read string $type mime type
   * @property-read string $encoding character encoding
   * @property-read integer $length byte length
   */
  abstract class Content {

    private $_type = 'text/plain';
    private $_encoding = '';

    /**
     * @param NetworkConnection $connection
     * @return bool|PromiseLike
     */
    abstract public function sendTo(NetworkConnection $connection);

    public function __construct(string $type = NULL, string $encoding = NULL) {
      if (isset($type)) {
        $this->_type = $type;
      }
      if (isset($encoding)) {
        $this->_encoding = $encoding;
      }
    }

    public function __isset($name) {
      switch ($name) {
      case 'type' :
      case 'encoding' :
      case 'length' :
        return TRUE;
      }
      return FALSE;
    }

    public function __get($name) {
      switch ($name) {
      case 'type' :
        return $this->_type;
      case 'encoding' :
        return $this->_encoding;
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

    public function getLength(): int {
      return 0;
    }
  }
}
