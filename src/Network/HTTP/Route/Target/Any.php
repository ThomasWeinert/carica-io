<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Route\Target {

  use Carica\Io\Network\HTTP\Request as HTTPRequest;
  use Carica\Io\Network\HTTP\Route\Target as RouteTarget;

  class Any extends RouteTarget {

    private $_methods = array();

    public function methods($methods): void {
      $this->_methods = array();
      if (is_string($methods)) {
        $methods = explode(' ', $methods);
      }
      foreach ($methods as $method) {
        $method = strtoupper(trim($method));
        if ($method !== '') {
          if (preg_match('(^[A-Z]{3,}$)', $method)) {
            $this->_methods[] = $method;
          } else {
            throw new \InvalidArgumentException(
              sprintf('Invalid http method name: "%s"', $method)
            );
          }
        }
      }
    }

    protected function validateMethod($method): bool {
      return (
        empty($this->_methods) ||
        in_array(strToUpper($method), $this->_methods, TRUE)
      );
    }

    public function validate(HTTPRequest $request) {
      if (!$this->validateMethod($request->method)) {
        return FALSE;
      }
      return array();
    }
  }
}
