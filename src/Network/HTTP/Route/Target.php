<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Route {

  use Carica\Io\Network\HTTP\Request as HTTPRequest;
  use Carica\Io\Network\HTTP\Response;

  abstract class Target {

    private $_callback;

    public function __construct(callable $callback) {
      $this->_callback = $callback;
    }

    public function getCallback(): callable {
      return $this->_callback;
    }

    public function __invoke(HTTPRequest $request): ?Response {
      $parameters = $this->prepare($request);
      if (is_array($parameters)) {
        return ($this->getCallback())($request, $parameters);
      }
      return NULL;
    }

    abstract public function prepare(HTTPRequest $request): ?array;
  }
}
