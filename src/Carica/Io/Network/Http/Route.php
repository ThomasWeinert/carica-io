<?php

namespace Carica\Io\Network\Http {

  use Carica\Io;

  class Route {

    private $_targets = array();

    public function match($path, Callable $callback) {
      $this->_targets[] = $target = new Route\Target($callback);
      $target->path($path);
      return $target;
    }

    public function __invoke($request) {
      return $this->handle($request);
    }

    private function handle(Request $request) {
      foreach ($this->_targets as $target) {
        if ($result = $target($request)) {
          return $result;
        }
      }
      return NULL;
    }

  }
}