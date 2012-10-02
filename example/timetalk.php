<?php

include('../src/Io/Loader.php');
Carica\Io\Loader::register();
use Carica\Io;

$loop = Io\Event\Loop\Factory::create();

$clients = array();

$server = new Io\Network\Server($loop);
$server->eventEmitter()->on(
  'connection',
  function ($stream) use ($loop, &$clients) {
    echo "Client connected: $stream\n";
    $clients[] = new Io\Network\Connection($loop, $stream);
  }
);

$loop->add(
  new Io\Event\Loop\Listener\Interval(
    1000,
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
    }
  )
);

$server->listen("tcp://0.0.0.0:8080");

$loop->run();