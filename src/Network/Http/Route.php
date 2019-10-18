<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http {

  use ArrayIterator;
  use Carica\Io\Network\Http\Route\Target\Any as AnyTarget;
  use Carica\Io\Network\Http\Route\Target\Match as MatchTarget;
  use Carica\Io\Network\Http\Route\Target\StartsWith as StartsWithTarget;
  use Iterator;
  use IteratorAggregate;

  /**
   * Simple routing class allowing to match a path against several targets
   *
   */
  class Route implements IteratorAggregate {

    private $_targets = array();

    /**
     * Attach a callback as an route target for any path
     *
     * @param Callable $callback
     * @return AnyTarget
     */
    public function any(Callable $callback): AnyTarget {
      $this->_targets[] = $target = new AnyTarget($callback);
      return $target;
    }

    /**
     * Attach a callback as a route target for the given path. Each part of the
     * path can be an string or a named match using {} around the name.
     *
     * Example: /some/path/{name}
     *
     * @param string $path
     * @param Callable $callback
     * @return MatchTarget
     */
    public function match($path, Callable $callback): MatchTarget {
      $this->_targets[] = $target = new MatchTarget($callback, $path);
      return $target;
    }

    /**
     * Attach a callback as a route target starting with the given path. This
     * is not unlike match but ignores if here is path has additional parts.
     *
     * @param string $path
     * @param callable $callback
     * @return StartsWithTarget
     */
    public function startsWith($path, Callable $callback): StartsWithTarget {
      $this->_targets[] = $target = new StartsWithTarget($callback, $path);
      return $target;
    }

    /**
     * Allow to trigger the route
     *
     * @param Request $request
     * @return NULL|Response
     */
    public function __invoke($request) {
      return $this->fire($request);
    }

    /**
     * Let each attached target executed for the request
     *
     * @param Request $request
     * @return Response|NULL
     */
    public function fire(Request $request): ?Response {
      foreach ($this->_targets as $target) {
        if ($result = $target($request)) {
          return $result;
        }
      }
      return NULL;
    }

    /**
     * Allow to iterate the attached route targets
     *
     * @see IteratorAggregate::getIterator()
     * @return Iterator
     */
    public function getIterator(): Iterator {
      return new ArrayIterator($this->_targets);
    }
  }
}
