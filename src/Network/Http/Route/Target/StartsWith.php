<?php

namespace Carica\Io\Network\Http\Route\Target {

  use Carica\Io\Network\Http;

  class StartsWith extends Match {

    protected function validateLength($length) {
      return $this->_pathLength <= $length;
    }
  }
}