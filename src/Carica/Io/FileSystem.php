<?php

namespace Carica\Io {

  /**
   * A wrapper/factory object for the file system.
   *
   * It provides and object interface for several file system related function
   * and can create instances of splFileInfo and splFileObject for others.
   *
   * The object is used in other classes to provide a tesable access to
   * the file system.
   */
  class FileSystem {

    /**
     * fileinfo provides weird results for css/js. This mapping is used to
     * force mimetypes by extension.
     *
     * @var array
     */
    private $_mimetypes = array(
      'css' => 'text/css',
      'html' => 'text/html',
      'js' => 'application/x-javascript'
    );

    /**
     * Create an return a splFileInfo instance for the filename
     *
     * @param string $filename
     * @return \SplFileInfo
     */
    public function getInfo($filename) {
      return new \SplFileInfo($filename);
    }

    /**
     * Create and return a splFileObject isntance for the filename
     *
     * @param string $filename
     * @param string $mode
     * @param resource $context
     * @return \SplFileObject
     */
    public function getFile($filename, $mode = 'r', $context = NULL) {
      if (NULL === $context) {
        return new \SplFileObject($filename, $mode, FALSE);
      } else {
        return new \SplFileObject($filename, $mode, FALSE, $context);
      }
    }

    /**
     * Open a file and return the resource
     *
     * @param string $filename
     * @param string $mode
     * @param resource $context
     * @return resource
     */
    public function getFileResource($filename, $mode = 'r', $context = NULL) {
      if (NULL === $context) {
        return fopen($filename, $mode, FALSE);
      } else {
        return fopen($filename, $mode, FALSE, $context);
      }
    }

    /**
     * Try to get the mimetype for an file.
     *
     * @param string $filename
     * @return string
     */
    public function getMimeType($filename) {
      $pattern = '(\.(?P<extension>[^/\\.]+)$)';
      if (preg_match($pattern, $filename, $matches)) {
        $extension = strtolower($matches['extension']);
        if (isset($this->_mimetypes[$extension])) {
          return $this->_mimetypes[$extension];
        }
      }
      return (function_exists('mime_content_type'))
        ? mime_content_type($filename)
        : 'application/octet-stream';
    }

    /**
     * Wrapper for the realpath() function
     *
     * @param string $path
     * @return string
     */
    public function getRealPath($path) {
      return realpath($path);
    }
  }
}