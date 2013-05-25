<?php

namespace Carica\Io {

  class FileSystem {

    public function getInfo($filename) {
      return new \SplFileInfo($filename);
    }

    public function getMimeType($filename) {
      if (function_exists('mime_content_type')) {
        return mime_content_type($filename);
      } else {
        return 'application/octet-stream';
      }
    }

    public function getFile($filename, $mode = 'r', $context = NULL) {
      if (NULL === $context) {
        return new \SplFileObject($filename, $mode, FALSE);
      } else {
        return new \SplFileObject($filename, $mode, FALSE, $context);
      }
    }

    public function getFileResource($filename, $mode = 'r', $context = NULL) {
      if (NULL === $context) {
        return fopen($filename, $mode, FALSE);
      } else {
        return fopen($filename, $mode, FALSE, $context);
      }
    }

    public function getRealPath($path) {
      return realpath($path);
    }
  }
}