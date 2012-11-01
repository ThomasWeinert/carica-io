<?php

include('../src/Io/Loader.php');
Carica\Io\Loader::register();
use Carica\Io\Event\Loop;

$loop = Loop\Factory::get();

$i = 0;

$loop->add(
  new Loop\Listener\Interval(
    1000,
    function () use (&$i) {
      echo $i++;
    }
  )
);
$loop->add(
  new Loop\Listener\Timeout(
    10000,
    function () use ($loop) {
      $loop->stop();
    }
  )
);

$loop->run();