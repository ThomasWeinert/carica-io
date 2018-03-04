<?php

namespace Carica\Io\Stream\Serial {

  use Carica\Io\Stream;

  class Factory {

    public const MODE_DEFAULT = 0;
    public const MODE_DIO = 1;
    public const MODE_GORILLA = 2;

    private static $_extensions = array(
      self::MODE_GORILLA => 'gorilla',
      //self::MODE_DIO => 'dio',
    );

    private static $_mode;

    public static function mode(int $mode = NULL) {
      if (
        isset($mode, self::$_extensions[$mode]) &&
        \extension_loaded(self::$_extensions[$mode])
      ) {
        self::$_mode = $mode;
      } elseif (NULL === self::$_mode) {
        foreach (self::$_extensions as $extensionMode => $extension) {
          if (\extension_loaded($extension)) {
            return self::$_mode = $extensionMode;
          }
        }
        self::$_mode = self::MODE_DEFAULT;
      }
      return self::$_mode;
    }

    /**
     * @param string $device
     * @param int $baud
     * @return Stream\Serial|Dio|Gorilla
     * @throws \LogicException
     */
    public static function create(string $device, $baud = Device::BAUD_DEFAULT) {
      switch (self::mode()) {
      case self::MODE_DIO :
        return new Dio($device, $baud);
      case self::MODE_GORILLA :
        return new Gorilla($device, $baud);
      case self::MODE_DEFAULT :
      default :
        return new Stream\Serial($device, $baud);
      }
    }

  }
}
