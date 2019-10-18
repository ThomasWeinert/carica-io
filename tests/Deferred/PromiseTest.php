<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;
  use PHPUnit\Framework\TestCase;

  class PromiseTest extends TestCase {

    /**
     * @covers \Carica\Io\Deferred\Promise::always
     */
    public function testAlways(): void {
      $function = static function() {
      };
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('always')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->always($function));
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::done
     */
    public function testDone(): void {
      $function = static function() {};
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('done')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->done($function));
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::fail
     */
    public function testFail(): void {
      $function = static function() {};
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('fail')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->fail($function));
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::then
     */
    public function testThenWithNullArguments(): void {
      $promise = $this
        ->getMockBuilder(Promise::class)
        ->disableOriginalConstructor()
        ->getMock();
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('then')
        ->with(NULL, NULL, NULL)
        ->willReturn($promise);

      $promise = new Promise($defer);
      $filterPromise = $promise->then();
      $this->assertInstanceOf(Promise::class, $filterPromise);
      $this->assertNotSame($filterPromise, $promise);
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::then
     */
    public function testThenWithFunctionArguments(): void {
      $promise = $this
        ->getMockBuilder(Promise::class)
        ->disableOriginalConstructor()
        ->getMock();
      $function = static function() {};
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('then')
        ->with($function, $function, $function)
        ->willReturn($promise);

      $promise = new Promise($defer);
      $filterPromise = $promise->then($function, $function, $function);
      $this->assertInstanceOf(Promise::class, $filterPromise);
      $this->assertNotSame($filterPromise, $promise);
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::progress
     */
    public function testProgress(): void {
      $function = static function() {};
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('progress')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->progress($function));
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::state
     */
    public function testState(): void {
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('state')
        ->willReturn(Io\Deferred::STATE_PENDING);

      $promise = new Promise($defer);
      $this->assertEquals(Io\Deferred::STATE_PENDING, $promise->state());
    }
  }
}
