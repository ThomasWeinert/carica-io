<?php
include(__DIR__.'/../../vendor/autoload.php');

use Carica\Io;

$mysqlOne = new Io\Deferred\MySQL(new mysqli('localhost'));
$mysqlTwo = new Io\Deferred\MySQL(new mysqli('localhost'));
$time = microtime(TRUE);
$debug = function($result) use ($time) {
  var_dump(iterator_to_array($result));
  var_dump(microtime(TRUE) - $time);
};
$queries = Io\Deferred::When(
  $mysqlOne("SELECT 'Query 1', SLEEP(5)")
    ->done($debug),
  $mysqlTwo("SELECT 'Query 2', SLEEP(1)")
    ->done($debug)
);
Io\Event\Loop\Factory::run($queries);


