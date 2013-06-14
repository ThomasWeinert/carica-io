<?php
include('../../../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Network\Http;

$route = new Http\Route();
$route->match(
  '/time',
  function ($request, $parameters) {
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
  function ($request) {
    $response = $request->createResponse();
    $response->content = new Http\Response\Content\File(
      __DIR__.'/index.html', 'text/html', 'utf-8'
    );
    return $response;
  }
);

$server = new Carica\Io\Network\Http\Server($route);
$server->listen(8080);

Carica\Io\Event\Loop\Factory::useLibevent(FALSE);
$loop = Carica\Io\Event\Loop\Factory::get();
$loop->setInterval(
  function() use ($loop) {
    var_dump($loop->count());
    gc_collect_cycles();
  },
  5000
);

Carica\Io\Event\Loop\Factory::run();