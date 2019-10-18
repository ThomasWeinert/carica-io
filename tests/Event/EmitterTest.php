<?php

namespace Carica\Io\Event {

  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;
  use UnexpectedValueException;

  include_once(__DIR__.'/../Bootstrap.php');

  /**
   * @covers \Carica\Io\Event\Emitter
   */
  class EmitterTest extends TestCase {

    public $emittedEvents = [];

    public function testOnAddListener(): void {
      /** @var callable|MockObject $event */
      $event = $this
        ->getMockBuilder(Emitter\Listener\On::class)
        ->disableOriginalConstructor()
        ->getMock();
      $emitter = new Emitter();
      $emitter->on('foo', $event);
      $this->assertEquals(
        [$event], $emitter->listeners('foo')
      );
    }

    public function testOnWrapsFunction(): void {
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        static function () {
        }
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
    public function testOnceAddListener(): void {
      /** @var callable|MockObject $event */
      $event = $this
        ->getMockBuilder(Emitter\Listener\Once::class)
        ->disableOriginalConstructor()
        ->getMock();
      $emitter = new Emitter();
      $emitter->once('foo', $event);
      $this->assertEquals(
        [$event], $emitter->listeners('foo')
      );
    }

    /**
     * @covers \Carica\Io\Event\Emitter::once
     */
    public function testOnceWrapsFunction(): void {
      $emitter = new Emitter();
      $emitter->once(
        'foo',
        static function () {}
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
    public function testRemoveListener(): void {
      /** @var Emitter\Listener|MockObject $listener */
      $listener = $this->createMock(Emitter\Listener::class);
      $emitter = new Emitter();
      $emitter->on('foo', $listener);
      $emitter->removeListener('foo', $listener);
      $this->assertCount(0, $emitter->listeners('foo'));
    }

    public function testRemoveAllListeners(): void {
      $emitter = new Emitter();
      $emitter->on('foo', $this->createMock(Emitter\Listener::class));
      $emitter->on('foo', $this->createMock(Emitter\Listener::class));
      $emitter->removeAllListeners('foo');
      $this->assertCount(0, $emitter->listeners('foo'));
    }

    public function testListenersReturnsEmptyArrayByDefault(): void {
      $emitter = new Emitter();
      $this->assertSame([], $emitter->listeners('foo'));
    }

    public function testListenersReturnsListenersForSpecifiedEvent(): void {
      $emitter = new Emitter();
      $emitter->on('foo', $this->createMock(Emitter\Listener::class));
      $emitter->on('bar', $this->createMock(Emitter\Listener::class));
      $this->assertCount(1, $emitter->listeners('bar'));
    }

    public function testEmitWithOneListener(): void {
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        function ($value) {
          $this->emittedEvents[] = $value;
        }
      );
      $emitter->emit('foo', 'success');
      $this->assertEquals(['success'], $this->emittedEvents);
    }

    public function testEmitWithTwoListeners(): void {
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        function () {
          $this->emittedEvents[] = 'one';
        }
      );
      $emitter->on(
        'foo',
        function () {
          $this->emittedEvents[] = 'two';
        }
      );
      $emitter->emit('foo');
      $this->assertEquals(['one', 'two'], $this->emittedEvents);
    }

    public function testEmitWithTwoListenersForDifferentEvents(): void {
      $emitter = new Emitter();
      $emitter->on(
        'foo',
        function () {
          $this->emittedEvents[] = 'fail';
        }
      );
      $emitter->on(
        'bar',
        function () {
          $this->emittedEvents[] = 'success';
        }
      );
      $emitter->emit('bar');
      $this->assertEquals(['success'], $this->emittedEvents);
    }

    public function testCallEventAfterDefining(): void {
      $emitter = new Emitter();
      $emitter->defineEvents(['foo']);
      $emitter->on(
        'foo',
        function () {
          $this->emittedEvents[] = 'one';
        }
      );
      $emitter->emit('foo');
      $this->assertEquals(['one'], $this->emittedEvents);
    }

    public function testCallEventAfterDefiningUsingAlias(): void {
      $emitter = new Emitter();
      $emitter->defineEvents(['foo' => 'bar']);
      $emitter->on(
        'bar',
        function () {
          $this->emittedEvents[] = 'one';
        }
      );
      $emitter->emit('bar');
      $this->assertEquals(['one'], $this->emittedEvents);
    }

    public function testCallEventAfterDefiningUsingSecondAlias(): void {
      $emitter = new Emitter();
      $emitter->defineEvents(['foo' => ['bar', 'foobar']]);
      $emitter->on(
        'foobar',
        function () {
          $this->emittedEvents[] = 'one';
        }
      );
      $emitter->emit('foobar');
      $this->assertEquals(['one'], $this->emittedEvents);
    }

    public function testEventAfterDefinitionWithUndefinedEventExpectingException(): void {
      $emitter = new Emitter();
      $emitter->defineEvents(['foo' => 'bar']);
      $this->expectException(UnexpectedValueException::class);
      $emitter->on(
        'invalid', static function () {}
      );
    }
  }
}
