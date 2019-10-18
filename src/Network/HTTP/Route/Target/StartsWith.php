<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Route\Target {

  class StartsWith extends Match {

    protected function validateLength(int $length): bool {
      return $this->_pathLength <= $length;
    }
  }
}
