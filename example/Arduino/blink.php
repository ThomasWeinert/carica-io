<?php

include('../../src/Carica/Io/Loader.php');
Carica\Io\Loader::register();

use Carica\Io;
use Carica\Io\Firmata;

$board = new Io\Firmata\Board(
  //new Io\Stream\Serial('COM3')
  new Io\Stream\Tcp('127.0.0.1', 5333)
);

$loop = Io\Event\Loop\Factory::get();


$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      echo "Firmata ".$board->version." active\n";

      $led = 13;
      $board->pinMode($led, Io\Firmata\PIN_STATE_OUTPUT);
      echo "PIN: $led\n";

      $loop->setInterval(
        function () use ($board, $led) {
          static $ledOn = TRUE;
          echo 'LED: '.($ledOn ? 'on' : 'off')."\n";
          $board->digitalWrite($led, $ledOn ? Io\Firmata\DIGITAL_HIGH : Io\Firmata\DIGITAL_LOW);
          $ledOn = !$ledOn;
        },
        1000
      );
    }
  )
  ->fail(
    function ($error) {
      echo $error."\n";
    }
  );


if ($board->isActive()) {
  $loop->run();
}


