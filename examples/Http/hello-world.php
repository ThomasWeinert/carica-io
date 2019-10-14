<?php
include(__DIR__.'/../../vendor/autoload.php');

use Carica\Io\Network\Http;

$loop = Carica\Io\Event\Loop\Factory::get();

$server = new Carica\Io\Network\Server($loop);
$server->events()->on(
  'connection',
  static function ($stream) use ($loop) {
    $request = new Http\Connection($loop, $stream);
    $request->events()->on(
      'request',
      static function (Http\Request $request) {
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
