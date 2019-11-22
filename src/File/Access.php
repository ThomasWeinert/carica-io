<?php
declare(strict_types=1);

namespace Carica\Io\File {

  use SplFileInfo;
  use SplFileObject;

  /**
   * A wrapper/factory object for the file system.
   *
   * It provides and object interface for several file system related function
   * and can create instances of splFileInfo and splFileObject for others.
   *
   * The object is used in other classes to provide a testable access to
   * the file system.
   */
  class Access {

    /**
     * fileinfo provides weird results for css/js. This mapping is used to
     * force mime types by extension.
     *
     * @var array
     */
    private $_mimeTypes = [
      'css' => 'text/css',
      'html' => 'text/html',
      'js' => 'application/x-javascript'
    ];

    /**
     * Create an return a splFileInfo instance for the filename
     *
     * @param string $filename
     * @return SplFileInfo
     */
    public function getInfo(string $filename): SplFileInfo {
      return new SplFileInfo($filename);
    }

    /**
     * Create and return a splFileObject instance for the filename
     *
     * @param string $filename
     * @param string $mode
     * @param resource $context
     * @return SplFileObject
     */
    public function getFile(string $filename, string $mode = 'rb', $context = NULL): ?SplFileObject {
      if (NULL === $context) {
        return new SplFileObject($filename, $mode, FALSE);
      }
      return new SplFileObject($filename, $mode, FALSE, $context);
    }

    /**
     * Open a file and return the resource
     *
     * @param string $filename
     * @param string $mode
     * @param resource $context
     * @return resource
     */
    public function getFileResource(string $filename, string $mode = 'rb', $context = NULL) {
      if (NULL === $context) {
        return fopen($filename, $mode, FALSE);
      }
      return fopen($filename, $mode, FALSE, $context);
    }

    /**
     * Try to get the mime type for an file.
     *
     * @param string $filename
     * @return string
     */
    public function getMimeType(string $filename): string {
      /** @lang PhpRegExp */
      $pattern = '(\.(?P<extension>[^/\\.]+)$)';
      if (preg_match($pattern, $filename, $matches)) {
        $extension = strtolower($matches['extension']);
        if (isset($this->_mimeTypes[$extension])) {
          return $this->_mimeTypes[$extension];
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
    public function getRealPath(string $path): string {
      return realpath($path);
    }
  }
}
