<?php
include('../src/Io/Loader.php');
Carica\Io\Loader::register();

$server = new Carica\Io\Network\Server();
$server->events()->on(
  'connection',
  function ($stream) {
    $request = new Carica\Io\Network\Http\Connection($stream);
    $request->events()->on(
      'request',
      function ($request) {
        echo $request->method.' '.$request->url."\n";
        $request->Connection()->write(
          "HTTP/1.1 200 OK\r\n".
          "Connection: close\r\n".
          "Content-Length: 11\r\n".
          "Content-Type: text/plain; charset=UTF-8\r\n\r\n".
          "Hallo Welt!"
        );
        $request->Connection()->close();
      }
    );
  }
);

$server->listen(8080);

Carica\Io\Event\Loop\Factory::run();