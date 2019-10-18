<?php
include(__DIR__.'/../../../vendor/autoload.php');

use Carica\Io\Network\HTTP;

$loop = Carica\Io\Event\Loop\Factory::get();

$route = new Carica\Io\Network\HTTP\Route();
$route->match('/', new HTTP\Route\File(__DIR__.'/index.html'));
$route->match(
  '/data',
  $webSocket = new HTTP\Route\WebSocket(
    static function($data) {
      var_dump($data);
    }
  )
);

$server = new Carica\Io\Network\HTTP\Server($loop, $route);
$server->listen();

$loop->setInterval(
  static function() use ($webSocket) {
    $webSocket->write(date(DATE_ATOM));
  },
  2000
);

$loop->run();
