<?php
declare(strict_types=1);

namespace Carica\Io\File\Access {

  use Carica\Io\File;
  use Carica\Io\File\Access as FileAccess;

  trait Aggregation {

    private $_fileAccess;

    /**
     * Create an return a File\Access instance, this is a factory providing access
     * to file system objects.
     *
     * @param FileAccess $fileAccess
     * @return FileAccess
     */
    public function fileAccess(File\Access $fileAccess = NULL): FileAccess {
      if (isset($fileAccess)) {
        $this->_fileAccess = $fileAccess;
      } elseif (NULL === $this->_fileAccess) {
        $this->_fileAccess = new File\Access();
      }
      return $this->_fileAccess;
    }
  }
}
