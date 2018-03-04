<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;
  use PHPUnit\Framework\TestCase;

  class PromiseTest extends TestCase {

    /**
     * @covers \Carica\Io\Deferred\Promise::__construct
     */
    public function testConstructor() {
      $defer = $this->createMock(Io\Deferred::class);
      $promise = new Promise($defer);
      $this->assertAttributeSame($defer, '_defer', $promise);
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::always
     */
    public function testAlways() {
      $function = function() {
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
    public function testDone() {
      $function = function() {};
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
    public function testFail() {
      $function = function() {};
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
    public function testThenWithNullArguments() {
      $promise = $this
        ->getMockBuilder(Promise::class)
        ->disableOriginalConstructor()
        ->getMock();
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('then')
        ->with(NULL, NULL, NULL)
        ->will($this->returnValue($promise));

      $promise = new Promise($defer);
      $filterPromise = $promise->then();
      $this->assertInstanceOf(Promise::class, $filterPromise);
      $this->assertNotSame($filterPromise, $promise);
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::then
     */
    public function testThenWithFunctionArguments() {
      $promise = $this
        ->getMockBuilder(Promise::class)
        ->disableOriginalConstructor()
        ->getMock();
      $function = function() {};
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('then')
        ->with($function, $function, $function)
        ->will($this->returnValue($promise));

      $promise = new Promise($defer);
      $filterPromise = $promise->then($function, $function, $function);
      $this->assertInstanceOf(Promise::class, $filterPromise);
      $this->assertNotSame($filterPromise, $promise);
    }

    /**
     * @covers \Carica\Io\Deferred\Promise::progress
     */
    public function testProgress() {
      $function = function() {};
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
    public function testState() {
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('state')
        ->will($this->returnValue(Io\Deferred::STATE_PENDING));

      $promise = new Promise($defer);
      $this->assertEquals(Io\Deferred::STATE_PENDING, $promise->state());
    }
  }
}
