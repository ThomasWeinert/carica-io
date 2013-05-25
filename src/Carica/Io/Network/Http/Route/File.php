<?php

namespace Carica\Io\Network\Http\Route {

  use Carica\Io;
  use Carica\Io\Network\Http;

  class File {

    use Io\FileSystem\Aggregation;

    private $_documentRoot = '';

    public function __construct($documentRoot) {
      $this->setDocumentRoot($documentRoot);
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

    public function __invoke() {
      return call_user_func_array(array($this, 'call'), func_get_args());
    }

    public function call(Http\Request $request, array $parameters) {
      if ($file = $this->getFileInfo($request)) {
        if ($file->isFile() && $file->isReadable()) {
          $response = $request->createResponse();
          $localFile = $file->getRealPath();
          $response->content = new Http\Response\Content\File(
            $localFile, $this->fileSystem()->getMimeType($localFile)
          );
          return $response;
        } else {
          return new Carica\Io\Network\Http\Response\Error(
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