<?php
include(__DIR__.'/../../vendor/autoload.php');

use Carica\Io\Network\HTTP;

$route = new Carica\Io\Network\HTTP\Route();
$route->match(
  '/hello/{name}',
  static function (HTTP\Request $request, $parameters) {
    $response = $request->createResponse(
      new HTTP\Response\Content\Text(
        'Hello '.$parameters['name']."!\n"
      )
    );
    return $response;
  }
);
$route->match(
  '/agent',
  static function (HTTP\Request $request) {
    $response = $request->createResponse(
      new HTTP\Response\Content\Text(
        $request->headers['User-Agent']
      )
    );
    return $response;
  }
);
$route->match(
  '/xml',
  static function (HTTP\Request $request) {
    $response = $request->createResponse($content = new HTTP\Response\Content\XML());
    $document = $content->document;
    $document->appendChild($root = $document->createElement('response'));
    foreach ($request->query as $name => $value) {
      $root->appendChild($parameter = $document->createElement('query-parameter'));
      $parameter->setAttribute('name', $name);
      if (NULL !== $value) {
        $parameter->appendChild($document->createTextNode($value));
      }
    }
    return $response;
  }
);
$route->match('/hello', new HTTP\Route\File(__DIR__.'/files/hello.html'));
$route->startsWith('/files', new HTTP\Route\Directory(__DIR__));

$loop = Carica\Io\Event\Loop\Factory::get();

$server = new Carica\Io\Network\HTTP\Server($loop, $route);
$server->listen();

$loop->run();
