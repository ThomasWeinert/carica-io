<?php
include(__DIR__.'/../vendor/autoload.php');

use Carica\Io\Event\Loop;

$loop = Loop\Factory::get();

$loop->setInterval(
  static function () {
    static $i = 0;
    echo $i++;
  },
  1000
);
$loop->setTimeout(
  static function () use ($loop) {
    $loop->stop();
  },
  10000
);

$loop->run();
