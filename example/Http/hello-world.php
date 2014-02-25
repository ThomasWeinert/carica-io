<?php
include(__DIR__.'/../../vendor/autoload.php');

use Carica\Io\Network\Http;

$server = new Carica\Io\Network\Server();
$server->events()->on(
  'connection',
  function ($stream) {
    $request = new Http\Connection($stream);
    $request->events()->on(
      'request',
      function (Http\Request $request) {
        echo $request->method.' '.$request->url."\n";
        $request->connection()->write(
          "HTTP/1.1 200 OK\r\n".
          "Connection: close\r\n".
          "Content-Length: 11\r\n".
          "Content-Type: text/plain; charset=UTF-8\r\n\r\n".
          "Hallo Welt!"
        );
        $request->connection()->close();
      }
    );
  }
);

$server->listen(8080);

Carica\Io\Event\Loop\Factory::run();