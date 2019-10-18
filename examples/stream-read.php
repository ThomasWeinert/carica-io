<?php
include(__DIR__.'/../vendor/autoload.php');

use Carica\Io\Event\Loop;
use Carica\Io\Stream;

$fileName = tempnam(sys_get_temp_dir(), 'io_');

$loop = Loop\Factory::get();
$write = fopen($fileName, 'wb');

$stream = new Stream\FileStream($loop, $fileName);
$stream->events()->on(
  Stream::EVENT_READ_DATA,
  static function($data) {
    echo $data;
  }
);
$stream->events()->on(
  Stream::EVENT_ERROR,
  static function($error) use ($loop) {
    echo $error;
    $loop->stop();
  }
);

$loop->setInterval(
  static function () use ($write) {
    fwrite($write, microtime(TRUE)."\n");
  },
  1000
);

$stream->open();
$loop->run();
