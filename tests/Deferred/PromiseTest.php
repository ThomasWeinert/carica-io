<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  /**
   * @covers \Carica\Io\Deferred\Promise
   */
  class PromiseTest extends TestCase {

    public function testAlways(): void {
      $function = static function() {};
      /** @var MockObject|Io\Deferred $defer */
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('always')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->always($function));
    }

    public function testDone(): void {
      $function = static function() {};
      /** @var MockObject|Io\Deferred $defer */
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('done')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->done($function));
    }

    public function testFail(): void {
      $function = static function() {};
      /** @var MockObject|Io\Deferred $defer */
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('fail')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->fail($function));
    }

    public function testThenWithNullArguments(): void {
      $promise = $this
        ->getMockBuilder(Promise::class)
        ->disableOriginalConstructor()
        ->getMock();
      /** @var MockObject|Io\Deferred $defer */
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

    public function testThenWithFunctionArguments(): void {
      $promise = $this
        ->getMockBuilder(Promise::class)
        ->disableOriginalConstructor()
        ->getMock();
      $function = static function() {};
      /** @var MockObject|Io\Deferred $defer */
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

    public function testProgress(): void {
      $function = static function() {};
      /** @var MockObject|Io\Deferred $defer */
      $defer = $this->createMock(Io\Deferred::class);
      $defer
        ->expects($this->once())
        ->method('progress')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->progress($function));
    }

    public function testState(): void {
      /** @var MockObject|Io\Deferred $defer */
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
