<?php

namespace Carica\Io\Event {

  include_once(__DIR__.'/../Bootstrap.php');

  class EmitterTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Event\Emitter::on
     */
    public function testOnAddListener() {
      $event = $this
        ->getMockBuilder('Carica\Io\Event\Emitter\Listener\On')
        ->disableOriginalConstructor()
        ->getMock();
      $emitter = new Emitter();
      $emitter->on('foo', $event);
      $this->assertEquals(
        array($event), $emitter->listeners('foo')
      );
    }

    /**
     * @covers Carica\Io\Event\Emitter::on
     */
    public function testOnWrapsFunction() {
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        function() {}
      );
      $events = $emitter->listeners('foo');
      $this->assertInstanceOf(
        'Carica\Io\Event\Emitter\Listener\On',
        $events[0]
      );
    }

    /**
     * @covers Carica\Io\Event\Emitter::once
     */
    public function testOnceAddListener() {
      $event = $this
        ->getMockBuilder('Carica\Io\Event\Emitter\Listener\Once')
        ->disableOriginalConstructor()
        ->getMock();
      $emitter = new Emitter();
      $emitter->once('foo', $event);
      $this->assertEquals(
        array($event), $emitter->listeners('foo')
      );
    }

    /**
     * @covers Carica\Io\Event\Emitter::once
     */
    public function testOnceWrapsFunction() {
      $emitter = new Emitter();
      $emitter->once(
        'foo',
        function() {}
      );
      $events = $emitter->listeners('foo');
      $this->assertInstanceOf(
        'Carica\Io\Event\Emitter\Listener\Once',
        $events[0]
      );
    }
  }
}
