<?php

include(__DIR__.'/../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io\Event\Loop;
use Carica\Io\Stream;

$loop = Loop\Factory::get();
$write = fopen('c:/tmp/sample.txt', 'w');

$stream = new Stream\File('c:/tmp/sample.txt');
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

$loop->setInterval(
  function () use ($write) {
    fwrite($write, microtime(TRUE)."\n");
  },
  1000
);

$stream->open();
$loop->run();