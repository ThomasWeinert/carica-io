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

    $led = 13;
    $board->pinMode($led, Io\Firmata\PIN_STATE_OUTPUT);
    echo "PIN: $led\n";

    $loop->add(
      new Io\Event\Loop\Listener\Interval(
        1000,
        function () use ($board, $led) {
          static $ledOn = FALSE;
          echo 'LED: '.($ledOn ? 'off' : 'on')."\n";
          $board->digitalWrite($led, $ledOn ? Io\Firmata\DIGITAL_LOW : Io\Firmata\DIGITAL_HIGH);
          $ledOn = !$ledOn;
        }
      )
    );
  }
);


if ($active) {
  $loop->run();
}


