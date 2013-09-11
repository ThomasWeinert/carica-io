<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io;
  use Carica\Io\Event;

  class Factory {

    /**
     * @var Event\Loop
     */
    private static $_globalLoop = NULL;

    private static $_useLibevent = FALSE;

    /**
     * Create a event loop
     *
     * @return Event\Loop
     */
    public static function create() {
      if (self::useLibevent()) {
        return new Libevent(event_base_new());
      } else {
        return new StreamSelect();
      }
    }

    /**
     * Getter/Setter for libevent usage, if TRUE is provided as an argument it will
     * only activate the use if the extension is installed.
     *
     * @param string $use
     * @return boolean
     */
    public static function useLibevent($use = NULL) {
      if (isset($use)) {
        self::$_useLibevent = $use ? NULL : FALSE;
      }
      if (NULL === self::$_useLibevent) {
        self::$_useLibevent = extension_loaded('libevent');
      }
      return self::$_useLibevent;
    }

    /**
     * Return a global event loop instance, create it if it does not exists yet.
     *
     * @return Event\Loop
     */
    public static function get() {
      if (is_null(self::$_globalLoop)) {
        self::$_globalLoop = self::create();
      }
      return self::$_globalLoop;
    }

    /**
     * Set the global event loop instance
     *
     * @param Event\Loop $loop
     */
    public static function set(Event\Loop $loop) {
      self::$_globalLoop = $loop;
    }

    /**
     * Destroy the global event loop
     *
     * @return Event\Loop
     */
    public static function reset() {
      self::$_useLibevent = NULL;
      self::$_globalLoop = NULL;
    }

    /**
     * Run the global event loop
     */
    public static function run(Io\Deferred\Promise $for = NULL) {
      self::get()->run($for);
    }
  }
}