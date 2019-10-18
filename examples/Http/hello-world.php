<?php
include(__DIR__.'/../../vendor/autoload.php');

use Carica\Io\Network\HTTP;

$loop = Carica\Io\Event\Loop\Factory::get();

$server = new Carica\Io\Network\Server($loop);
$server->events()->on(
  Carica\Io\Network\Server::EVENT_CONNECTION,
  static function ($stream) use ($loop) {
    $request = new HTTP\Connection($loop, $stream);
    $request->events()->on(
      HTTP\Connection::EVENT_REQUEST_RECEIVED,
      static function (HTTP\Request $request) {
        echo $request->method.' '.$request->url."\n";
        $request->connection()->write(
          "HTTP/1.1 200 OK\r\n".
          "Connection: close\r\n".
          "Content-Length: 11\r\n".
          "Content-Type: text/plain; charset=UTF-8\r\n\r\n".
          'Hallo Welt!'
        );
        $request->connection()->close();
      }
    );
  }
);

$server->listen();

$loop->run();
