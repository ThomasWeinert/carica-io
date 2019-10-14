<?php

namespace Carica\Io\Network\Http\Route {

  use Carica\Io\Network\Http;

  abstract class Target {

    private $_callback = NULL;

    public function __construct(Callable $callback) {
      $this->_callback = $callback;
    }

    public function getCallback() {
      return $this->_callback;
    }

    public function __invoke($request) {
      $parameters = $this->validate($request);
      if (is_array($parameters)) {
        return ($this->getCallback())($request, $parameters);
      }
      return FALSE;
    }

    abstract public function validate(Http\Request $request);
  }
}
