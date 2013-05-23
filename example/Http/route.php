<?php
include('../../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Network\Http;

$route = new Carica\Io\Network\Http\Route();
$route->match(
  '/hello/{name}',
  function ($request, $parameters) {
    $response = $request->createResponse();
    $response->content = new Http\Response\Content\String(
      "Hello ".$parameters['name']."!\n"
    );
    return $response;
  }
);
$route->match(
  '/agent',
  function ($request, $parameters) {
    $response = $request->createResponse();
    $response->content = new Http\Response\Content\String(
      $request->headers['User-Agent']
    );
    return $response;
  }
);
$route->match(
  '/xml',
  function ($request, $parameters) {
    $response = $request->createResponse();
    $response->content = new Http\Response\Content\Xml();
    $dom = $response->content->document;
    $dom->appendChild($root = $dom->createElement('response'));
    foreach ($request->query as $name => $value) {
      $root->appendChild($parameter = $dom->createElement('query-parameter'));
      $parameter->setAttribute('name', $name);
      if (NULL !== $value) {
        $parameter->appendChild($dom->createTextNode($value));
      }
    }
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
          $request->connection()->close();
        } else {
          $request->connection()->write(
            "HTTP/1.1 200 OK\r\n".
            "Connection: close\r\n".
            "Content-Length: 11\r\n".
            "Content-Type: text/plain; charset=UTF-8\r\n\r\n".
            "Hallo Welt!"
          );
        }
        $request->connection()->close();
      }
    );
  }
);

$server->listen(8080);

Carica\Io\Event\Loop\Factory::run();