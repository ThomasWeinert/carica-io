<?php

include('../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Event\Loop;
use Carica\Io\Stream;

$loop = Loop\Factory::get();
$write = fopen('c:/temp/sample.txt', 'w');

$stream = new Stream\File('c:/temp/sample.txt');
$stream->events()->on(
  'read-data',
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
  $loop->setInterval(
    function () use ($write) {
      fwrite($write, microtime(TRUE)."\n");
    },
    1000
  );

  $loop->run();
}