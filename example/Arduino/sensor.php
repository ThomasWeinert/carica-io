<?php

include('../../src/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io;
use Carica\Io\Firmata;

$board = new Io\Firmata\Board(
  //new Io\Stream\SerialPort(3)
  new Io\Stream\Tcp('127.0.0.1', 5333)
);

$loop = Io\Event\Loop\Factory::get();


$active = $board->activate(
  function ($error = NULL) use ($board, $loop) {
    if (isset($error)) {
      echo $error."\n";
      return;
    }
    echo "Firmata ".implode('.', $board->version)." active\n";

    $pin = 14;
    $board->pinMode($pin, Io\Firmata\PIN_STATE_ANALOG);
    echo "PIN: $pin\n";

    $board->analogRead(
      $pin,
      function($value) {
        $barLength = round($value * 0.08);
        echo str_pad($value, 4, 0, STR_PAD_LEFT), ' ';
        echo str_repeat('=', $barLength), "\n";
      }
    );
  }
);


if ($active) {
  $loop->run();
}


