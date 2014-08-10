<?php

namespace Carica\Io\File\Access {

  use Carica\Io\File;

  trait Aggregation {

    private $_fileAccess = NULL;

    /**
     * Create an return a File\Access instance, this is a factory providing access
     * to file system objects.
     *
     * @param File\Access $fileAccess
     * @return File\Access
     */
    public function fileAccess(File\Access $fileAccess = NULL) {
      if (isset($fileAccess)) {
        $this->_fileAccess = $fileAccess;
      } elseif (NULL === $this->_fileAccess) {
        $this->_fileAccess = new File\Access();
      }
      return $this->_fileAccess;
    }
  }
}