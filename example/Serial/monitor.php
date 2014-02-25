<?php
include(__DIR__.'/../../vendor/autoload.php');

use Carica\Io;

$port = 'COM9:';
$baud = 57000;

Io\Stream\Serial\Factory::useDio(FALSE);
$serial = Io\Stream\Serial\Factory::create($port, $baud);

$serial
  ->events()
  ->on(
    'read-data',
    function ($data) {
      echo $data;
    }
  );
$serial->open();

Io\Event\Loop\Factory::run();