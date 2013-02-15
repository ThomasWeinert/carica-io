<?php

use Carica\Io\Stream\SerialPort;

include('../../src/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io;
use Carica\Io\Firmata;

$board = new Firmata\Board(
  $stream = new Io\Stream\SerialPort(3)
);

$debug = function ($data) {
  if (!empty($data)) {
    $list = new \Carica\Io\ByteArray(1);
    $list->fromString($data, TRUE);
    var_dump($list->asHex());
  }
};
$stream->events()->on('read', $debug);
$stream->events()->on('write', $debug);

$loop = Io\Event\Loop\Factory::get();

$board->events()->on(
  'reportversion',
  function () use ($board) {
    echo 'Firmata version: '.implode('.', $board->version)."\n";
  }
);
$board->events()->on(
  'queryfirmware',
  function () use ($board) {
    echo 'Firmware version: '.$board->firmware['name'].' '.implode('.', $board->firmware['version'])."\n";
  }
);

$active = $board->activate(
  function ($error = NULL) {
    if (isset($error)) {
      var_dump($error);
      echo $error."\n";
      return;
    }
    echo "activated\n";
  }
);

if ($active) {
  $loop->run();
}


