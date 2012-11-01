<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;

  class PromiseTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Deferred\Promise::always
     */
    public function testAlways() {
      $function = function() {
      };
      $defer = $this->getMock('Carica\Io\Deferred');
      $defer
        ->expects($this->once())
        ->method('always')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->always($function));
    }

    /**
     * @covers Carica\Io\Deferred\Promise::done
     */
    public function testDone() {
      $function = function() {};
      $defer = $this->getMock('Carica\Io\Deferred');
      $defer
        ->expects($this->once())
        ->method('done')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->done($function));
    }

    /**
     * @covers Carica\Io\Deferred\Promise::fail
     */
    public function testFail() {
      $function = function() {};
      $defer = $this->getMock('Carica\Io\Deferred');
      $defer
        ->expects($this->once())
        ->method('fail')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->fail($function));
    }

  }
}
