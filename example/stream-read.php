<?php

include('../src/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Event\Loop;
use Carica\Io\Stream;

$loop = Loop\Factory::create();
$write = fopen('c:/temp/sample.txt', 'w');

$stream = new Stream\FileReader($loop, 'c:/temp/sample.txt');
$stream->events()->on(
  'data',
  function($data) {
    echo $data;
  }
);
$stream->events()->on(
  'error',
  function($error) use ($loop) {
    echo $error;
    $loop->stop();
  }
);

if ($stream->open()) {
  $loop->add(
    new Loop\Listener\Interval(
      1000,
      function () use ($loop, $write) {
        fwrite($write, microtime(TRUE)."\n");
      }
    )
  );

  $loop->run();
}