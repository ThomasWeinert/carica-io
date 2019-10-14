<?php
include(__DIR__.'/../../../vendor/autoload.php');

use Carica\Io\Network\Http;

$route = new Http\Route();
$route->match(
  '/time',
  function (Http\Request $request) {
    $response = $request->createResponse();
    $response->content = new Http\Response\Content\Xml();
    $dom = $response->content->document;
    $dom->appendChild($rootNode = $dom->createElement('time'));
    $rootNode->appendChild($dom->createTextNode(date('Y-m-d H:i:s')));
    return $response;
  }
);
$route->match(
  '/',
  function (Http\Request $request) {
    $response = $request->createResponse();
    $response->content = new Http\Response\Content\File(
      __DIR__.'/index.html', 'text/html', 'utf-8'
    );
    return $response;
  }
);

$server = new Carica\Io\Network\Http\Server($route);
$server->listen(8080);

$loop = Carica\Io\Event\Loop\Factory::get();
$loop->setInterval(
  function() use ($loop) {
    gc_collect_cycles();
  },
  5000
);

Carica\Io\Event\Loop\Factory::run();
