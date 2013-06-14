<?php

namespace Carica\Io\Network\Http\Response {

  use Carica\Io;
  use Carica\Io\Network\Http;

  class Error extends Http\Response {

    private $_template = '
      <html>
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
      $this->content = new Http\Response\Content\Html();
      if (NULL === $message) {
        $message = $this->_statusStrings[$this->status];
      }
      $this->content->document->loadHtml(
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