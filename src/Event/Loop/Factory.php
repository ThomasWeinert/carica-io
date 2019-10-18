<?php
declare(strict_types=1);

namespace Carica\Io\Event\Loop {

  use Carica\Io\Deferred\PromiseLike;
  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\Event\Loop\StreamSelect as StreamSelectLoop;

  class Factory {

    /**
     * @var EventLoop
     */
    private static $_loopInstance;

    /**
     * Return a global event loop instance, create it if it does not exists yet.
     *
     * @param callable $loopGenerator
     * @return EventLoop
     */
    public static function get(callable $loopGenerator = NULL): EventLoop {
      if (NULL === self::$_loopInstance) {
        if (NULL !== $loopGenerator) {
          $loop = $loopGenerator();
          if ($loop instanceof EventLoop) {
            return self::$_loopInstance = $loop;
          }
          throw new \LogicException('Loop generator callback did not return a loop instance.');
        }
        return self::$_loopInstance = new StreamSelectLoop();
      }
      return self::$_loopInstance;
    }

    /**
     * Set the global event loop instance
     *
     * @param EventLoop $loop
     */
    public static function set(EventLoop $loop): void {
      self::$_loopInstance = $loop;
    }

    /**
     * Destroy the global event loop
     */
    public static function reset(): void {
      self::$_loopInstance = NULL;
    }

    /**
     * Run the global event loop
     * @param PromiseLike $for
     */
    public static function run(PromiseLike $for = NULL): void {
      self::get()->run($for);
    }
  }
}
