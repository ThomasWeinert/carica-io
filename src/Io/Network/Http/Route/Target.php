<?php

namespace Carica\Io\Network\Http\Route {

  use Carica\Io\Network\Http;

  class Target {
    
    private $_pathMatches = array();
    private $_pathParameters = array();
    private $_pathLength = 0;
    
    private $_methods = array();
    
    private $_callback = NULL;
    
    public function __construct(Callable $callback) {
      $this->_callback = $callback;
    }

    public function path($path) {
      $parts = explode('/', $path);
      $this->_pathLength = count($parts);
      foreach ($parts as $index => $part) {
        if (0 === strpos($part, '{')) {
          $this->_pathParameters[$index] = substr($part, 1, -1);
        } else {
          $this->_pathMatches[$index] = $part;
        }
      } 
    }
    
    public function methods($methods) {
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
            throw new InvalidArgumentException(
              sprintf('Invalid http method name: "%s"', $method)
            );
          }
        }
      }
    }    
    
    public function __invoke($request) {
      return $this->handle($request);
    }
    
    private function handle(Http\Request $request) {
      $parameters = array();
      if (!(empty($this->_methods) || in_array($request->method, $this->_methods))) {
        return FALSE;
      }
      if ($this->_pathLength > 0) {
        $parts = explode('/', $request->path);
        if (count($parts) !== $this->_pathLength) {
          return FALSE; 
        }
        foreach ($this->_pathMatches as $index => $match) {
          if ($parts[$index] != $match) {
            return FALSE;
          }
        }
        foreach ($this->_pathParameters as $index => $name) {
          if ($parts[$index] === '') {
            return FALSE;
          }
          $parameters[$name] = $parts[$index];
        }
      }
      return call_user_func($this->_callback, $request, $parameters);
    }
  }
}