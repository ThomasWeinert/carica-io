<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Route {

  use Carica\Io\File\Access as FileAccess;
  use Carica\Io\File\HasAccess as HasFileAccess;
  use Carica\Io\Network\Http\Request as HTTPRequest;
  use Carica\Io\Network\Http\Response\Error as ErrorResponse;
  use Carica\Io\Network\Http\Response\Content as ResponseContent;

  class Directory implements HasFileAccess {

    use FileAccess\Aggregation;

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
      foreach ($encodings as $mimeType => $encoding) {
        $this->setEncoding($mimeType, $encoding);
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

    public function call(HTTPRequest $request) {
      if ($file = $this->getFileInfo($request)) {
        if ($file->isFile() && $file->isReadable()) {
          $response = $request->createResponse();
          $localFile = $file->getRealPath();
          $mimeType = $this->fileAccess()->getMimeType($localFile);
          $encoding = $this->getEncoding($mimeType);
          $response->content = new ResponseContent\File(
            $localFile, $mimeType, $encoding
          );
          return $response;
        }
        return new ErrorResponse($request, 403);
      }
      return NULL;
    }

    /**
     * @param HTTPRequest $request
     * @return \SplFileInfo|FALSE
     */
    private function getFileInfo(HTTPRequest $request) {
      if ($localFile = $this->fileAccess()->getRealPath($this->_documentRoot.$request->path)) {
        if (0 === strpos($localFile, $this->_documentRoot.DIRECTORY_SEPARATOR)) {
          return $this->fileAccess()->getInfo($localFile);
        }
      }
      return FALSE;
    }
  }
}
