<?php
include('../../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();

$route = new Carica\Io\Network\Http\Route();
$route->match(
  '/hello/{name}',
  function ($request, $parameters) {
    /*
     * @todo implement response class
     */
    echo "Hello ".$parameters['name']."\n"; 
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
          /*
           * @todo implement response handling
           */
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