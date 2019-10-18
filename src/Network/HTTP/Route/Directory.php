<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Route {

  use Carica\Io\File\Access as FileAccess;
  use Carica\Io\File\HasAccess as HasFileAccess;
  use Carica\Io\Network\HTTP\Request as HTTPRequest;
  use Carica\Io\Network\HTTP\Response as HTTPResponse;
  use Carica\Io\Network\HTTP\Response\Error as ErrorResponse;
  use Carica\Io\Network\HTTP\Response\Content as ResponseContent;

  class Directory implements HasFileAccess {

    use FileAccess\Aggregation;

    private $_documentRoot = '';

    private $_encodings = [
      'text/plain' => 'utf-8',
      'text/html' => 'utf-8',
      'text/javascript' => 'utf-8',
      'application/x-javascript' => 'utf-8',
      'application/xml' => 'utf-8'
    ];

    public function __construct(string $documentRoot, array $encodings = []) {
      $this->setDocumentRoot($documentRoot);
      foreach ($encodings as $mimeType => $encoding) {
        $this->setEncoding($mimeType, $encoding);
      }
    }

    public function setDocumentRoot(string $documentRoot): void {
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

    public function setEncoding(string $mimeType, string $encoding): void {
      $this->_encodings[$mimeType] = $encoding;
    }

    public function getEncoding($mimeType): string {
      return $this->_encodings[$mimeType] ?? '';
    }

    public function __invoke(HTTPRequest $request): ?HTTPResponse {
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
