<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Route {

  use Carica\Io\Network\Http\Request as HTTPRequest;

  abstract class Target {

    private $_callback;

    public function __construct(callable $callback) {
      $this->_callback = $callback;
    }

    public function getCallback(): callable {
      return $this->_callback;
    }

    public function __invoke($request) {
      $parameters = $this->validate($request);
      if (is_array($parameters)) {
        return ($this->getCallback())($request, $parameters);
      }
      return FALSE;
    }

    abstract public function validate(HTTPRequest $request);
  }
}
