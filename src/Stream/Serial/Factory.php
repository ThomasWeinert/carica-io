<?php

namespace Carica\Io\Stream\Serial {

  use Carica\Io\Stream;

  class Factory {

    /**
     * Set use dio to false, by default dio is not a valid
     * stream resource so it uses a interval listener on the
     * event loop. It is no able to use a stream listener.
     *
     * @var boolean
     */
    private static $_useDio = FALSE;

    public static function useDio($use = NULL) {
      if (isset($use)) {
        if ($use && extension_loaded('dio')) {
          self::$_useDio = TRUE;
        } elseif (!$use) {
          self::$_useDio = FALSE;
        } else {
          throw new \LogicException('Extension "dio" not available.');
        }
      } elseif (NULL == self::$_useDio) {
        self::$_useDio = extension_loaded('dio');
      }
      return self::$_useDio;
    }

    public static function create($device, $baud) {
      if (self::useDio()) {
        return new Dio($device, $baud);
      } else {
        return new Stream\Serial($device, $baud);
      }
    }

  }
}
