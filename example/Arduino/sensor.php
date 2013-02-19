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

$board
  ->activate()
  ->done(
    function () use ($board, $loop) {
      echo "Firmata ".$board->version." active\n";

      $sensorPin = 16;
      $ledPin = 13;

      $board->pins[$sensorPin]->mode = Io\Firmata\PIN_STATE_ANALOG;
      $board->pins[$ledPin]->mode = Io\Firmata\PIN_STATE_OUTPUT;

      echo "Sensor: $sensorPin\n";
      echo "Led: $ledPin\n";

      $board->analogRead(
        $sensorPin,
        function($value) use ($board, $ledPin) {
          $barLength = round($value * 0.07);
          echo str_pad($value, 4, 0, STR_PAD_LEFT), ' ';
          echo str_repeat('=', $barLength), "\n";
          $board->pins[$ledPin]->digital = $value > 600;
        }
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

