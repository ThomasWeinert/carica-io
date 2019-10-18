<?php
include(__DIR__.'/../../vendor/autoload.php');

use Carica\Io;

$loop = Io\Event\Loop\Factory::get();

$port = 'COM7:';
$baud = Io\Stream\Serial\Device::BAUD_57600;

$serial = new Io\Stream\SerialStream($loop, $port, $baud);

$serial
  ->events()
  ->on(
    Io\Stream\SerialStream::EVENT_READ_DATA,
    static function ($data) {
      echo $data;
    }
  );
$serial->open();

$loop->run();
