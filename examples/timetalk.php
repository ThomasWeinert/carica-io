<?php
include(__DIR__.'/../vendor/autoload.php');

use Carica\Io;

$loop = Io\Event\Loop\Factory::get();

$clients = array();

$server = new Io\Network\Server($loop);
$server->events()->on(
  Io\Network\Server::EVENT_CONNECTION,
  static function ($stream) use (&$clients, $loop) {
    echo "Client connected: $stream\n";
    $clients[] = new Io\Network\Connection($loop, $stream);
  }
);

$loop->setInterval(
  static function () use (&$clients) {
    echo 'Send time to '.count($clients)." clients\n";
    foreach ($clients as $index => $client) {
      /**
       * @var Io\Network\Connection $client
       */
      if ($client->isActive()) {
        $client->write(date(DATE_ATOM)."\n");
      } else {
        echo "Removing inactive client\n";
        unset($clients[$index]);
      }
    }
  },
  1000
);

$server->listen(8080);

$loop->run();
