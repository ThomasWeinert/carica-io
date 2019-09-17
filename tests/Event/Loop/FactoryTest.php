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

    public function testCreate() {
      $loop = Factory::create();
      $this->assertInstanceOf(Loop::class, $loop);
    }

    public function testCreateStreamSelectExplicit() {
      $loop = Factory::create(array());
      $this->assertInstanceOf(StreamSelect::class, $loop);
    }

    public function testGetAfterSet() {
      $loop = $this->createMock(Loop::class);
      Factory::set($loop);
      $this->assertSame($loop, Factory::get());
    }

    public function testGetImplicitCreate() {
      $loop = Factory::get();
      $this->assertInstanceOf(Loop::class, $loop);
    }

    public function testRun() {
      $loop = $this->createMock(Loop::class);
      $loop
        ->expects($this->once())
        ->method('run');
      Factory::set($loop);
      Factory::run();
    }
  }
}
