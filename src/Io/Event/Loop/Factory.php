<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event;

  class Factory {

    /**
     * @var Carica\Io\Event\Loop
     */
    private static $_globalLoop = NULL;

    /**
     * Create a event loop
     *
     * @return Carica\Io\Event\Loop
     */
    public static function create() {
      return new StreamSelect();
    }

    /**
     * Return a global event loop instance, create it if it does not exists yet.
     *
     * @return Carica\Io\Event\Loop
     */
    public static function get() {
      if (is_null(self::$_globalLoop)) {
        self::$_globalLoop = self::create();
      }
      return self::$_globalLoop;
    }

    /**
     * Destroy the global event loop
     *
     * @return Carica\Io\Event\Loop
     */
    public static function reset() {
      if (!is_null(self::$_globalLoop)) {
        self::$_globalLoop = NULL;
      }
    }

    /**
     * Run the global event loop
     */
    public static function run() {
      self::get()->run();
    }
  }
}