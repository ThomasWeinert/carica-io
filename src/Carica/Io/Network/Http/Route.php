<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Route {

    private $_targets = array();

    public function any(Callable $callback) {
      $this->_targets[] = $target = new Route\Target\Any($callback);
      return $target;
    }

    public function match($path, Callable $callback) {
      $this->_targets[] = $target = new Route\Target\Match($callback, $path);
      return $target;
    }

    public function startsWith($path, Callable $callback) {
      $this->_targets[] = $target = new Route\Target\StartsWith($callback, $path);
      return $target;
    }

    public function __invoke($request) {
      return $this->fire($request);
    }

    public function fire(Request $request) {
      foreach ($this->_targets as $target) {
        if ($result = $target($request)) {
          return $result;
        }
      }
      return NULL;
    }
  }
}