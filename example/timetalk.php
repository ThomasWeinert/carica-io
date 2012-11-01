<?php

include('../src/Io/Loader.php');
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

$server->listen(8080);

$loop->run();