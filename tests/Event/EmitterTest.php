<?php

namespace Carica\Io\Event {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../Bootstrap.php');

  class EmitterTest extends TestCase {

    public $emittedEvents = array();

    /**
     * @covers \Carica\Io\Event\Emitter::on
     */
    public function testOnAddListener() {
      $event = $this
        ->getMockBuilder(Emitter\Listener\On::class)
        ->disableOriginalConstructor()
        ->getMock();
      $emitter = new Emitter();
      $emitter->on('foo', $event);
      $this->assertEquals(
        array($event), $emitter->listeners('foo')
      );
    }

    /**
     * @covers \Carica\Io\Event\Emitter::on
     */
    public function testOnWrapsFunction() {
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        function() {}
      );
      $events = $emitter->listeners('foo');
      $this->assertInstanceOf(
        Emitter\Listener\On::class,
        $events[0]
      );
    }

    /**
     * @covers \Carica\Io\Event\Emitter::once
     */
    public function testOnceAddListener() {
      $event = $this
        ->getMockBuilder(Emitter\Listener\Once::class)
        ->disableOriginalConstructor()
        ->getMock();
      $emitter = new Emitter();
      $emitter->once('foo', $event);
      $this->assertEquals(
        array($event), $emitter->listeners('foo')
      );
    }

    /**
     * @covers \Carica\Io\Event\Emitter::once
     */
    public function testOnceWrapsFunction() {
      $emitter = new Emitter();
      $emitter->once(
        'foo',
        function() {}
      );
      $events = $emitter->listeners('foo');
      $this->assertInstanceOf(
        Emitter\Listener\Once::class,
        $events[0]
      );
    }

    /**
     * @covers \Carica\Io\Event\Emitter::removeListener
     */
    public function testRemoveListener() {
      $listener = $this->createMock(Emitter\Listener::class);
      $emitter = new Emitter();
      $emitter->on('foo', $listener);
      $emitter->removeListener('foo', $listener);
      $this->assertCount(0, $emitter->listeners('foo'));
    }

    /**
     * @covers \Carica\Io\Event\Emitter::removeAllListeners
     */
    public function testRemoveAllListeners() {
      $emitter = new Emitter();
      $emitter->on('foo', $this->createMock(Emitter\Listener::class));
      $emitter->on('foo', $this->createMock(Emitter\Listener::class));
      $emitter->removeAllListeners('foo');
      $this->assertCount(0, $emitter->listeners('foo'));
    }

    /**
     * @covers \Carica\Io\Event\Emitter::listeners
     */
    public function testListernersReturnsEmptyArrayByDefault() {
      $emitter = new Emitter();
      $this->assertSame(array(), $emitter->listeners('foo'));
    }

    /**
     * @covers \Carica\Io\Event\Emitter::listeners
     */
    public function testListernersReturnsListenersForSpecifiedEvent() {
      $emitter = new Emitter();
      $emitter->on('foo', $this->createMock(Emitter\Listener::class));
      $emitter->on('bar', $this->createMock(Emitter\Listener::class));
      $this->assertCount(1, $emitter->listeners('bar'));
    }

    /**
     * @covers \Carica\Io\Event\Emitter::emit
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
     * @covers \Carica\Io\Event\Emitter::emit
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
     * @covers \Carica\Io\Event\Emitter::emit
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

    /**
     * @covers \Carica\Io\Event\Emitter
     */
    public function testCallEventAfterDefining() {
      $that = $this;
      $emitter = new Emitter();
      $emitter->defineEvents(array('foo'));
      $emitter->on(
        'foo',
        function() use ($that) {
          $that->emittedEvents[] = 'one';
        }
      );
      $emitter->emit('foo');
      $this->assertEquals(array('one'), $that->emittedEvents);
    }

    /**
     * @covers \Carica\Io\Event\Emitter
     */
    public function testCallEventAfterDefiningUsingAlias() {
      $that = $this;
      $emitter = new Emitter();
      $emitter->defineEvents(array('foo' => 'bar'));
      $emitter->on(
        'bar',
        function() use ($that) {
          $that->emittedEvents[] = 'one';
        }
      );
      $emitter->emit('bar');
      $this->assertEquals(array('one'), $that->emittedEvents);
    }

    /**
     * @covers \Carica\Io\Event\Emitter
     */
    public function testCallEventAfterDefiningUsingSecondAlias() {
      $that = $this;
      $emitter = new Emitter();
      $emitter->defineEvents(array('foo' => array('bar', 'foobar')));
      $emitter->on(
        'foobar',
        function() use ($that) {
          $that->emittedEvents[] = 'one';
        }
      );
      $emitter->emit('foobar');
      $this->assertEquals(array('one'), $that->emittedEvents);
    }

    /**
     * @covers \Carica\Io\Event\Emitter
     */
    public function testEventAfterDefinitionWithUndefinedEventExpectingException() {
      $emitter = new Emitter();
      $emitter->defineEvents(array('foo' => 'bar'));
      $this->expectException(\UnexpectedValueException::class);
      $emitter->on('invalid', function() {});
    }
  }
}
