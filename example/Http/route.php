<?php
include('../../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Network\Http;

$route = new Carica\Io\Network\Http\Route();
$route->match(
  '/hello/{name}',
  function ($request, $parameters) {
    $response = new Http\Response($request->connection());
    $response->content = new Http\Response\Content\String(
      "Hello ".$parameters['name']."\n"
    );
    return $response;
  }
);
$route->match(
  '/agent',
  function ($request, $parameters) {
    $response = new Http\Response($request->connection());
    $response->content = new Http\Response\Content\String(
      (string)$request->headers['User-Agent']
    );
    return $response;
  }
);

$server = new Carica\Io\Network\Server();
$server->events()->on(
  'connection',
  function ($stream) use ($route) {
    $request = new Carica\Io\Network\Http\Connection($stream);
    $request->events()->on(
      'request',
      function ($request) use ($route) {
        echo $request->method.' '.$request->url."\n";
        if ($response = $route($request)) {
          $response->send();
          $request->Connection()->close();
        } else {
          $request->Connection()->write(
            "HTTP/1.1 200 OK\r\n".
            "Connection: close\r\n".
            "Content-Length: 11\r\n".
            "Content-Type: text/plain; charset=UTF-8\r\n\r\n".
            "Hallo Welt!"
          );
        }
        $request->Connection()->close();
      }
    );
  }
);

$server->listen(8080);

Carica\Io\Event\Loop\Factory::run();