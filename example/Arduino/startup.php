<?php

use Carica\Io\Stream\SerialPort;

include('../../src/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io;
use Carica\Io\Firmata;

$board = new Firmata\Board(
  $stream = new Io\Stream\SerialPort(3)
);

$loop = Io\Event\Loop\Factory::get();

$board->events()->on(
  'reportversion',
  function () use ($board) {
    echo 'Firmata version: '.$board->version."\n";
  }
);
$board->events()->on(
  'queryfirmware',
  function () use ($board) {
    echo 'Firmware version: '.$board->firmware."\n";
  }
);

$active = $board->activate(
  function ($error = NULL) {
    if (isset($error)) {
      echo $error."\n";
      return;
    }
    echo "activated\n";
  }
);

if ($board->isActive()) {
  $loop->run();
}


