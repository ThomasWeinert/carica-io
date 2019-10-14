<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Route {

  use Carica\Io;
  use Carica\Io\Network\Http;

  class Directory {

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

    public function setDocumentRoot($documentRoot): void {
      if ($directory = $this->fileAccess()->getRealPath($documentRoot)) {
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

    public function setEncoding($mimeType, $encoding): void {
      $this->_encodings[$mimeType] = (string)$encoding;
    }

    public function getEncoding($mimeType): string {
      return $this->_encodings[$mimeType] ?? '';
    }

    public function __invoke(...$arguments) {
      return $this->call(...$arguments);
    }

    public function call(Http\Request $request) {
      if ($file = $this->getFileInfo($request)) {
        if ($file->isFile() && $file->isReadable()) {
          $response = $request->createResponse();
          $localFile = $file->getRealPath();
          $mimeType = $this->fileAccess()->getMimeType($localFile);
          $encoding = $this->getEncoding($mimeType);
          $response->content = new Http\Response\Content\File(
            $localFile, $mimeType, $encoding
          );
          return $response;
        }
        return new Http\Response\Error($request, 403);
      }
      return NULL;
    }

    /**
     * @param Http\Request $request
     * @return \SplFileInfo|FALSE
     */
    private function getFileInfo(Http\Request $request) {
      if ($localFile = $this->fileAccess()->getRealPath($this->_documentRoot.$request->path)) {
        if (0 === strpos($localFile, $this->_documentRoot.DIRECTORY_SEPARATOR)) {
          return $this->fileAccess()->getInfo($localFile);
        }
      }
      return FALSE;
    }
  }
}
