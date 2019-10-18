<?php
include(__DIR__.'/../../../vendor/autoload.php');

use Carica\Io\Network\Http;

$loop = Carica\Io\Event\Loop\Factory::get();

$route = new Carica\Io\Network\Http\Route();
$route->match('/', new Http\Route\File(__DIR__.'/index.html'));
$route->match(
  '/data',
  $webSocket = new Http\Route\WebSocket(
    static function($data) {
      var_dump($data);
    }
  )
);

$server = new Carica\Io\Network\Http\Server($loop, $route);
$server->listen();

$loop->setInterval(
  static function() use ($webSocket) {
    $webSocket->write(date(DATE_ATOM));
  },
  2000
);

$loop->run();
