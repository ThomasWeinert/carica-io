<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io;
  use Carica\Io\Event;

  class Factory {

    const USE_STREAMSELECT = 'streamselect';
    const USE_LIBEVENT = 'libevent';
    const USE_REACT = 'react';
    const USE_ALERT_REACTOR = 'alert-reactor';

    /**
     * @var Event\Loop
     */
    private static $_globalLoop = NULL;

    /**
     * @var null|int
     */
    private static $_useImplementation = NULL;

    private static $_priority = array();

    private static $_defaultPriority = array(
      self::USE_REACT,
      self::USE_LIBEVENT,
      self::USE_STREAMSELECT
    );

    /**
     * Create a event loop
     *
     * @param array $priority
     * @return Event\Loop
     */
    public static function create(array $priority = NULL) {
      switch (self::getImplementation($priority)) {
      case self::USE_REACT :
        return new React();
      case self::USE_ALERT_REACTOR :
        return new AlertReactor();
      case self::USE_LIBEVENT :
        return new Libevent(event_base_new());
      default :
        return new StreamSelect();
      }
    }

    /**
     * Determine the implementation that should be used
     *
     * @param array $priority
     * @return string
     */
    public static function getImplementation(array $priority = NULL) {
      $priority = self::getPriority($priority);
      if (NULL === self::$_useImplementation ||
          !in_array(self::$_useImplementation, $priority)) {
        foreach ($priority as $implementation) {
          switch ($implementation) {
          case self::USE_ALERT_REACTOR :
            if (interface_exists('\\Alert\\Reactor')) {
              return self::$_useImplementation = self::USE_ALERT_REACTOR;
            }
            break;
          case self::USE_REACT :
            if (interface_exists('\\React\\EventLoop\\LoopInterface')) {
              return self::$_useImplementation = self::USE_REACT;
            }
            break;
          case self::USE_LIBEVENT :
            if (extension_loaded('libevent')) {
              return self::$_useImplementation = self::USE_LIBEVENT;
            }
            break;
          }
        }
        self::$_useImplementation = self::USE_STREAMSELECT;
      }
      return self::$_useImplementation;
    }

    private static function getPriority($priority) {
      if (NULL == self::$_priority) {
        self::$_priority = self::$_defaultPriority;
      }
      if (NULL === $priority) {
        $priority = self::$_priority;
      } else {
        self::$_priority = $priority;
      }
      return $priority;
    }

    /**
     * Return a global event loop instance, create it if it does not exists yet.
     *
     * @param null $priority
     * @return Event\Loop
     */
    public static function get($priority = NULL) {
      if (is_null(self::$_globalLoop)) {
        self::$_globalLoop = self::create($priority);
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
      self::$_priority = self::$_defaultPriority;
      self::$_useImplementation = NULL;
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