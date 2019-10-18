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
    private static $_globalLoopInstance;

    /**
     * Return a global event loop instance, create it if it does not exists yet.
     *
     * @param callable $loopGenerator
     * @return EventLoop
     */
    public static function get(callable $loopGenerator = NULL): EventLoop {
      if (NULL === self::$_globalLoopInstance) {
        if (NULL !== $loopGenerator) {
          $loop = $loopGenerator();
          if ($loop instanceof EventLoop) {
            return self::$_globalLoopInstance = $loop;
          }
          throw new \LogicException('Loop generator callback dit not return a loop instance.');
        }
        return self::$_globalLoopInstance = new StreamSelectLoop();
      }
      return self::$_globalLoopInstance;
    }

    /**
     * Set the global event loop instance
     *
     * @param EventLoop $loop
     */
    public static function set(EventLoop $loop): void {
      self::$_globalLoopInstance = $loop;
    }

    /**
     * Destroy the global event loop
     */
    public static function reset(): void {
      self::$_globalLoopInstance = NULL;
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
