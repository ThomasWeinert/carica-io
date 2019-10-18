<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Response {

  use Carica\Io\Network\HTTP\Request as HTTPRequest;
  use Carica\Io\Network\HTTP\Response as HTTPResponse;
  use DOMDocument;

  /**
   * @property DOMDocument $document
   */
  class Error extends HTTPResponse {

    private $_template = '
      <html lang="en">
        <head>
          <title>%1$s - %2$s</title>
        </head>
        <body>
          <h1>%1$s - %2$s</h1>
          <div>%3$s</div>
          <h4>powered by Carica Io</h4>
        </body>
      </html>';

    public function __construct(HTTPRequest $request, int $status = 500, string $message = NULL) {
      parent::__construct($request->connection());
      $this->setStatus($status);
      $this->content = $content = new Content\HTML();
      if (NULL === $message) {
        $message = $this->_statusStrings[$this->status];
      }
      $content->document->loadHTML(
        sprintf(
          $this->_template,
          $status,
          htmlspecialchars($message),
          htmlspecialchars($request->url)
        )
      );
    }

  }
}
