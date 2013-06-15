<?php

include(__DIR__.'/../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();
use Carica\Io\Event\Loop;

$loop = Loop\Factory::get();

$i = 0;

$loop->setInterval(
  function () use (&$i) {
    echo $i++;
  },
  1000
);
$loop->setTimeout(
  function () use ($loop) {
    $loop->stop();
  },
  10000
);

$loop->run();