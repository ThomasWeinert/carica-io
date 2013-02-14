<?php

include('../../src/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io;
use Carica\Io\Firmata;

$board = new Io\Firmata\Board(
  $stream = new Io\Stream\SerialPort(3)
);

$loop = Io\Event\Loop\Factory::get();

$active = $board->activate(
  function ($error = NULL) use ($board, $loop) {
    if (isset($error)) {
      echo $error."\n";
      return;
    }
    echo "Firmata ".implode('.', $board->getVersion())." active\n";

    $led = 13;
    $board->pinMode($led, Firmata\PIN_STATE_OUTPUT);
    echo "PIN: $led\n";

    $loop->add(
      new Io\Event\Loop\Listener\Interval(
        1000,
        function () use ($board, $led) {
          static $ledOn = FALSE;
          echo 'LED: '.($ledOn ? 'off' : 'on')."\n";
          $board->digitalWrite($led, $ledOn ? Firmata\DIGITAL_LOW : Firmata\DIGITAL_HIGH);
          $ledOn = !$ledOn;
        }
      )
    );
  }
);


if ($active) {
  $loop->run();
}


