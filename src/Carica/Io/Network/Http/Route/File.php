<?php

namespace Carica\Io\Network\Http\Route {

  use Carica\Io;
  use Carica\Io\Network\Http;

  class File {

    use Io\File\Access\Aggregation;

    private $_documentRoot = '';

    private $_encodings = array(
      'text/plain' => 'utf-8',
      'text/html' => 'utf-8',
      'text/javascript' => 'utf-8',
      'application/x-javascript' => 'utf-8',
      'application/xml' => 'utf-8'
    );

    public function __construct($documentRoot, array $encodings = array()) {
      $this->setDocumentRoot($documentRoot);
      foreach ($encodings as $mimetype => $encoding) {
        $this->setEncoding($mimetype, $encoding);
      }
    }

    public function setDocumentRoot($documentRoot) {
      if ($directory = $this->fileSystem()->getRealPath($documentRoot)) {
        $this->_documentRoot = $directory;
        return;
      }
      throw new \LogicException(
        sprintf(
         'Invalid document root: Directory "%s" not found.',
         $documentRoot
        )
      );
    }

    public function setEncoding($mimetype, $encoding) {
      $this->_encodings[$mimetype] = (string)$encoding;
    }

    public function getEncoding($mimetype) {
      if (isset($this->_encodings[$mimetype])) {
        return $this->_encodings[$mimetype];
      }
      return '';
    }

    public function __invoke() {
      return call_user_func_array(array($this, 'call'), func_get_args());
    }

    public function call(Http\Request $request) {
      if ($file = $this->getFileInfo($request)) {
        if ($file->isFile() && $file->isReadable()) {
          $response = $request->createResponse();
          $localFile = $file->getRealPath();
          $mimetype = $this->fileSystem()->getMimeType($localFile);
          $encoding = $this->getEncoding($mimetype);
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
     * @param Http\Request $request
     * @return \SplFileInfo
     */
    private function getFileInfo(Http\Request $request) {
      if ($localFile = $this->fileSystem()->getRealPath($this->_documentRoot.$request->path)) {
        if (0 === strpos($localFile, $this->_documentRoot.DIRECTORY_SEPARATOR)) {
          return $this->fileSystem()->getInfo($localFile);
        }
      }
      return FALSE;
    }
  }
}