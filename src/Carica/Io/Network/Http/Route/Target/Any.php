<?php

namespace Carica\Io\Network\Http\Route\Target {

  use Carica\Io\Network\Http;

  class Any extends Http\Route\Target {

    private $_methods = array();

    public function methods($methods) {
      $this->_methods = array();
      if (is_string($methods)) {
        $methods = explode(' ', $methods);
      }
      foreach ($methods as $method) {
        $method = strToUpper(trim($method));
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

    protected function validateMethod($method) {
      return (
        empty($this->_methods) ||
        in_array(strToUpper($method), $this->_methods)
      );
    }

    public function validate(Http\Request $request) {
      if (!$this->validateMethod($request->method)) {
        return FALSE;
      }
      return array();
    }
  }
}