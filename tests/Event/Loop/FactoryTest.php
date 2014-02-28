<?php

namespace Carica\Io\Event\Loop {

  include_once(__DIR__.'/../../Bootstrap.php');

  use Carica\Io\Event;


  /**
   * @covers Carica\Io\Event\Loop\Factory
   */
  class FactoryTest extends \PHPUnit_Framework_TestCase {

    public function tearDown() {
      Factory::reset();
    }

    public function testCreate() {
      $loop = Factory::create();
      $this->assertInstanceOf('Carica\\Io\\Event\\Loop', $loop);
    }

    public function testCreateStreamSelectExplicit() {
      $loop = Factory::create(array());
      $this->assertInstanceOf('Carica\\Io\\Event\\Loop\\StreamSelect', $loop);
    }

    public function testGetAfterSet() {
      $loop = $this->getMock('Carica\\Io\\Event\\Loop');
      Factory::set($loop);
      $this->assertSame($loop, Factory::get());
    }

    public function testGetImplicitCreate() {
      $loop = Factory::get();
      $this->assertInstanceOf('Carica\\Io\\Event\\Loop', $loop);
    }

    public function testRun() {
      $loop = $this->getMock('Carica\\Io\\Event\\Loop');
      $loop
        ->expects($this->once())
        ->method('run');
      Factory::set($loop);
      Factory::run();
    }
  }
}