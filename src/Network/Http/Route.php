<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  /**
   * Simple routing class allo to match a path against several targets
   *
   */
  class Route implements \IteratorAggregate {

    private $_targets = array();

    /**
     * Attach a callback as an route target for any path
     *
     * @param Callable $callback
     * @return \Carica\Io\Network\Http\Route\Target\Any
     */
    public function any(Callable $callback) {
      $this->_targets[] = $target = new Route\Target\Any($callback);
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
     * @return \Carica\Io\Network\Http\Route\Target\Match
     */
    public function match($path, Callable $callback) {
      $this->_targets[] = $target = new Route\Target\Match($callback, $path);
      return $target;
    }

    /**
     * Attach a callback as a route target starting with the given path. This
     * is not unlike match but ignores if here is path has additional parts.
     *
     * @param string $path
     * @param callable $callback
     * @return Route\Target\StartsWith
     */
    public function startsWith($path, Callable $callback) {
      $this->_targets[] = $target = new Route\Target\StartsWith($callback, $path);
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
    public function fire(Request $request) {
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
     * @return \Iterator
     */
    public function getIterator() {
      return new \ArrayIterator($this->_targets);
    }
  }
}