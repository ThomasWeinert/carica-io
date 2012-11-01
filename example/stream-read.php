<?php

include('../src/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Event\Loop;
use Carica\Io\Stream;

$loop = Loop\Factory::get();
$write = fopen('c:/temp/sample.txt', 'w');

$stream = new Stream\FileReader('c:/temp/sample.txt');
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
      function () use ($write) {
        fwrite($write, microtime(TRUE)."\n");
      }
    )
  );

  $loop->run();
}