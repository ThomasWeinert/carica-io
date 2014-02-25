<?php

namespace Carica\Io\Event {

  include_once(__DIR__.'/../Bootstrap.php');

  class EmitterTest extends \PHPUnit_Framework_TestCase {

    public $emittedEvents = array();

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

    /**
     * @covers Carica\Io\Event\Emitter::removeListener
     */
    public function testRemoveListener() {
      $listener = $this->getMock('Carica\Io\Event\Emitter\Listener');
      $emitter = new Emitter();
      $emitter->on('foo', $listener);
      $emitter->removeListener('foo', $listener);
      $this->assertCount(0, $emitter->listeners('foo'));
    }

    /**
     * @covers Carica\Io\Event\Emitter::removeAllListeners
     */
    public function testRemoveAllListeners() {
      $emitter = new Emitter();
      $emitter->on('foo', $this->getMock('Carica\Io\Event\Emitter\Listener'));
      $emitter->on('foo', $this->getMock('Carica\Io\Event\Emitter\Listener'));
      $emitter->removeAllListeners('foo');
      $this->assertCount(0, $emitter->listeners('foo'));
    }

    /**
     * @covers Carica\Io\Event\Emitter::listeners
     */
    public function testListernersReturnsEmptyArrayByDefault() {
      $emitter = new Emitter();
      $this->assertSame(array(), $emitter->listeners('foo'));
    }

    /**
     * @covers Carica\Io\Event\Emitter::listeners
     */
    public function testListernersReturnsListenersForSpecifiedEvent() {
      $emitter = new Emitter();
      $emitter->on('foo', $this->getMock('Carica\Io\Event\Emitter\Listener'));
      $emitter->on('bar', $this->getMock('Carica\Io\Event\Emitter\Listener'));
      $this->assertCount(1, $emitter->listeners('bar'));
    }

    /**
     * @covers Carica\Io\Event\Emitter::emit
     */
    public function testEmitWithOneListener() {
      $that = $this;
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        function($value) use ($that) {
          $that->emittedEvents[] = $value;
        }
      );
      $emitter->emit('foo', 'success');
      $this->assertEquals(array('success'), $that->emittedEvents);
    }

    /**
     * @covers Carica\Io\Event\Emitter::emit
     */
    public function testEmitWithTwoListeners() {
      $that = $this;
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        function() use ($that) {
          $that->emittedEvents[] = 'one';
        }
      );
      $emitter->on(
        'foo',
        function () use ($that) {
          $that->emittedEvents[] = 'two';
        }
      );
      $emitter->emit('foo');
      $this->assertEquals(array('one', 'two'), $that->emittedEvents);
    }

    /**
     * @covers Carica\Io\Event\Emitter::emit
     */
    public function testEmitWithTwoListenersfordifferentEvents() {
      $that = $this;
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        function() use ($that) {
          $that->emittedEvents[] = 'fail';
        }
      );
      $emitter->on(
        'bar',
        function () use ($that) {
          $that->emittedEvents[] = 'success';
        }
      );
      $emitter->emit('bar');
      $this->assertEquals(array('success'), $that->emittedEvents);
    }
  }
}
