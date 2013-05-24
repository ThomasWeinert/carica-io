<?php

namespace Carica\Io {

  class FileSystem {

    public function getInfo($filename) {
      return new \SplFileInfo($filename);
    }

    public function getFile($filename, $mode = 'r', $context = NULL) {
      if (NULL === $context) {
        return new \SplFileObject($filename, $mode, FALSE, $context);
      } else {
        return new \SplFileObject($filename, $mode, FALSE);
      }
    }

    public function getRealPath($path) {
      return realpath($path);
    }
  }
}