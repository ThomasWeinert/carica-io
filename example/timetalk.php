<?php

include(__DIR__.'/../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();
use Carica\Io;

$loop = Io\Event\Loop\Factory::get();

$clients = array();

$server = new Io\Network\Server();
$server->events()->on(
  'connection',
  function ($stream) use (&$clients) {
    echo "Client connected: $stream\n";
    $clients[] = new Io\Network\Connection($stream);
  }
);

$loop->setInterval(
  function () use (&$clients) {
    echo "Send time to ".count($clients)." clients\n";
    foreach ($clients as $index => $client) {
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