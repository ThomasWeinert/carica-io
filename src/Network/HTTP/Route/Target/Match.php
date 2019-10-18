<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Route\Target {

  use Carica\Io\Network\HTTP\Request as HTTPRequest;

  class Match extends Any {

    private $_pathMatches = array();
    private $_pathParameters = array();
    protected $_pathLength = 0;

    public function __construct(callable $callback, string $path) {
      parent::__construct($callback);
      $this->setPath( $path);
    }

    private function setPath(string $path): void {
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

    public function validate(HTTPRequest $request) {
      if (FALSE === parent::validate($request)) {
        return FALSE;
      }
      $parameters = array();
      if ($this->_pathLength > 0) {
        $parts = explode('/', $request->path);
        if (!$this->validateLength(count($parts))) {
          return FALSE;
        }
        foreach ($this->_pathMatches as $index => $match) {
          if ($parts[$index] !== $match) {
            return FALSE;
          }
        }
        foreach ($this->_pathParameters as $index => $name) {
          $parameters[$name] = $parts[$index];
        }
      }
      return $parameters;
    }

    protected function validateLength(int $length): bool {
      return $this->_pathLength === $length;
    }
  }
}
