<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Route\Target {

  use Carica\Io\Network\HTTP\Request as HTTPRequest;
  use Carica\Io\Network\HTTP\Route\Target as RouteTarget;
  use InvalidArgumentException;

  class Any extends RouteTarget {

    private $_methods = [];

    public function methods(string ...$methods): void {
      $this->_methods = [];
      foreach ($methods as $method) {
        $method = strtoupper(trim($method));
        if ($method !== '') {
          if (preg_match('(^[A-Z]{3,}$)', $method)) {
            $this->_methods[] = $method;
          } else {
            throw new InvalidArgumentException(
              sprintf('Invalid http method name: "%s"', $method)
            );
          }
        }
      }
    }

    protected function validateMethod(string $method): bool {
      return (
        empty($this->_methods) ||
        in_array(strToUpper($method), $this->_methods, TRUE)
      );
    }

    /**
     * @param HTTPRequest $request
     * @return array|null
     */
    public function prepare(HTTPRequest $request): ?array {
      if (!$this->validateMethod($request->method)) {
        return NULL;
      }
      return [];
    }
  }
}
