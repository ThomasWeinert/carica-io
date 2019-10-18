<?php

namespace Carica\Io\Event\Loop {

  include_once(__DIR__.'/../../Bootstrap.php');

  use PHPUnit\Framework\TestCase;

  /**
   * @covers \Carica\Io\Event\Loop\Clock
   */
  class ClockTest extends TestCase {

    public function testSetTimeout(): void {
      $loop = new Clock();
      $success = FALSE;
      $loop->setTimeout(
        static function () use (&$success) {
          $success = TRUE;
        },
        100
      );
      $loop->tick(99);
      $this->assertFalse($success);
      $loop->tick(1);
      $this->assertTrue($success);
    }

    public function testSetTimeoutOnlyCalledOnce(): void {
      $loop = new Clock();
      $counter = 0;
      $loop->setTimeout(
        static function () use (&$counter) {
          $counter++;
        },
        100
      );
      $loop->tick(1000);
      $this->assertEquals(1, $counter);
    }

    public function testSetInterval(): void {
      $loop = new Clock();
      $counter = 0;
      $loop->setInterval(
        static function () use (&$counter) {
          $counter++;
        },
        100
      );
      $loop->tick(1000);
      $this->assertEquals(10, $counter);
    }
  }
}
