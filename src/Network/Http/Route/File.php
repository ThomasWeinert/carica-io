<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Route {

  use Carica\Io\Network\Http\Response as HTTPResponse;
  use SplFileInfo;
  use Carica\Io\File\Access as FileAccess;
  use Carica\Io\File\HasAccess as HasFileAccess;
  use Carica\Io\Network\Http\Request as HTTPRequest;
  use Carica\Io\Network\Http\Response\Error as ErrorResponse;
  use Carica\Io\Network\Http\Response\Content as ResponseContent;

  class File implements HasFileAccess {

    use FileAccess\Aggregation;

    private $_file = '';

    private $_encoding;

    public function __construct(string $file, string $encoding = 'utf-8') {
      $this->setFile($file);
      $this->_encoding = $encoding;
    }

    public function setFile(string $fileName): void {
      if ($file = $this->fileAccess()->getRealPath($fileName)) {
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

    public function __invoke(HTTPRequest $request): ?HTTPResponse {
      if ($file = $this->getFileInfo()) {
        if ($file->isFile() && $file->isReadable()) {
          $response = $request->createResponse();
          $localFile = $file->getRealPath();
          $mimeType = $this->fileAccess()->getMimeType($localFile);
          $encoding = $this->_encoding;
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
     * @return SplFileInfo|NULL
     */
    private function getFileInfo(): ?SplFileInfo {
      if ($localFile = $this->fileAccess()->getRealPath($this->_file)) {
        return $this->fileAccess()->getInfo($localFile);
      }
      return NULL;
    }
  }
}
