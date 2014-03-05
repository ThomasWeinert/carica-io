<?php

namespace Carica\Io\Event\Loop {

  include_once(__DIR__.'/../../Bootstrap.php');

  use Carica\Io\Event;

  /**
   * @covers Carica\Io\Event\Loop\Fake
   */
  class ClockTest extends \PHPUnit_Framework_TestCase {

    public function testSetTimeout() {
      $loop = new Clock();
      $success = FALSE;
      $loop->setTimeout(
        function () use (&$success) {
          $success = TRUE;
        },
        100
      );
      $loop->tick(99);
      $this->assertFalse($success);
      $loop->tick(1);
      $this->assertTrue($success);
    }
  }
}