<?php

namespace Carica\Io\FileSystem {

  use Carica\Io;

  trait Aggregation {

    private $_fileSystem = NULL;

    /**
     * Create an return a FileSystem instance
     *
     * @param Io\FileSystem $fileSystem
     * @return Io\FileSystem
     */
    public function fileSystem(Io\FileSystem $fileSystem = NULL) {
      if (isset($fileSystem)) {
        $this->_fileSystem = $fileSystem;
      } elseif (NULL === $this->_fileSystem) {
        $this->_fileSystem = new Io\FileSystem();
      }
      return $this->_fileSystem;
    }
  }
}