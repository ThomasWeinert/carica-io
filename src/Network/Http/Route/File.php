<?php

namespace Carica\Io\Network\Http\Route {

  use Carica\Io;
  use Carica\Io\Network\Http;

  class File {

    use Io\File\Access\Aggregation;

    private $_file = '';

    private $_encoding = 'utf-8';

    public function __construct($file, $encoding = 'utf-8') {
      $this->setFile($file);
      $this->_encoding = $encoding;
    }

    public function setFile($documentRoot) {
      if ($file = $this->fileAccess()->getRealPath($documentRoot)) {
        $this->_file = $file;
        return;
      }
      throw new \LogicException(
        sprintf(
         'Invalid file: "%s" not found.',
         $file
        )
      );
    }

    public function __invoke(...$arguments) {
      return $this->call(...$arguments);
    }

    public function call(Http\Request $request) {
      if ($file = $this->getFileInfo()) {
        if ($file->isFile() && $file->isReadable()) {
          $response = $request->createResponse();
          $localFile = $file->getRealPath();
          $mimetype = $this->fileAccess()->getMimeType($localFile);
          $encoding = $this->_encoding;
          $response->content = new Http\Response\Content\File(
            $localFile, $mimetype, $encoding
          );
          return $response;
        } else {
          return new Http\Response\Error(
            $request, 403
          );
        }
      }
      return NULL;
    }

    /**
     * @return \SplFileInfo
     */
    private function getFileInfo() {
      if ($localFile = $this->fileAccess()->getRealPath($this->_file)) {
        return $this->fileAccess()->getInfo($localFile);
      }
      return FALSE;
    }
  }
}
