<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event\Loop;
  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');


  /**
   * @covers \Carica\Io\Event\Loop\Factory
   */
  class FactoryTest extends TestCase {

    public function tearDown(): void {
      Factory::reset();
    }

    public function testGetAfterSet(): void {
      $loop = $this->createMock(Loop::class);
      Factory::set($loop);
      $this->assertSame($loop, Factory::get());
    }

    public function testGetImplicitCreate(): void {
      $loop = Factory::get();
      $this->assertNotNull($loop);
    }

    public function testRun(): void {
      $loop = $this->createMock(Loop::class);
      $loop
        ->expects($this->once())
        ->method('run');
      Factory::set($loop);
      Factory::run();
    }
  }
}
