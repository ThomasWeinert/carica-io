<?php

namespace Carica\Io\FileSystem {

  trait Aggregation {

    private $_fileSystem = NULL;

    public function fileSystem(\Carica\Io\FileSystem $fileSystem = NULL) {
      if (isset($fileSystem)) {
        $this->_fileSystem = $fileSystem;
      } elseif (NULL === $this->_fileSystem) {
        $this->_fileSystem = new \Carica\Io\FileSystem();
      }
      return $this->_fileSystem;
    }
  }
}