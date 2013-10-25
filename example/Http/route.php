<?php
include(__DIR__.'/../../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Network\Http;

$route = new Carica\Io\Network\Http\Route();
$route->match(
  '/hello/{name}',
  function (Http\Request $request, $parameters) {
    $response = $request->createResponse();
    $response->content = new Http\Response\Content\String(
      "Hello ".$parameters['name']."!\n"
    );
    return $response;
  }
);
$route->match(
  '/agent',
  function (Http\Request $request) {
    $response = $request->createResponse();
    $response->content = new Http\Response\Content\String(
      $request->headers['User-Agent']
    );
    return $response;
  }
);
$route->match(
  '/xml',
  function (Http\Request $request) {
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
$route->match('/hello', new Http\Route\File(__DIR__.'/files/hello.html'));
$route->startsWith('/files', new Http\Route\Directory(__DIR__));

$server = new Carica\Io\Network\Http\Server($route);
$server->listen(8080);

Carica\Io\Event\Loop\Factory::run();