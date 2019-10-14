<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Response {

  use Carica\Io\Network\Http;
  use DOMDocument;

  /**
   * @property DOMDocument $document
   */
  class Error extends Http\Response {

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

    public function __construct(Http\Request $request, $status = 500, $message = NULL) {
      parent::__construct($request->connection());
      $this->setStatus($status);
      $this->content = $content = new Http\Response\Content\HTML();
      if (NULL === $message) {
        $message = $this->_statusStrings[$this->status];
      }
      $content->document->loadHtml(
        sprintf(
          $this->_template,
          (int)$status,
          htmlspecialchars($message),
          htmlspecialchars($request->url)
        )
      );
    }

  }
}
