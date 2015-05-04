<?php
include(__DIR__.'/../../vendor/autoload.php');

use Carica\Io;

$port = 'COM7:';
$baud = Io\Stream\Serial\Device::BAUD_57600;

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