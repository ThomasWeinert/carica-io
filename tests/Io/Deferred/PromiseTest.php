<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;

  class PromiseTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Deferred\Promise::_construct
     */
    public function testConstructor() {
      $defer = $this->getMock('Carica\Io\Deferred');
      $promise = new Promise($defer);
      $this->assertAttributeSame($defer, '_defer', $promise);
    }

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

    /**
     * @covers Carica\Io\Deferred\Promise::pipe
     */
    public function testPipeWithNullArguments() {
      $promise = $this
        ->getMockBuilder('Carica\Io\Deferred\Promise')
        ->disableOriginalConstructor()
        ->getMock();
      $defer = $this->getMock('Carica\Io\Deferred');
      $defer
        ->expects($this->once())
        ->method('pipe')
        ->with(NULL, NULL, NULL)
        ->will($this->returnValue($promise));

      $promise = new Promise($defer);
      $filterPromise = $promise->pipe();
      $this->assertInstanceOf('Carica\Io\Deferred\Promise', $filterPromise);
      $this->assertNotSame($filterPromise, $promise);
    }

    /**
     * @covers Carica\Io\Deferred\Promise::pipe
     */
    public function testPipeWithFunctionArguments() {
      $promise = $this
        ->getMockBuilder('Carica\Io\Deferred\Promise')
        ->disableOriginalConstructor()
        ->getMock();
      $function = function() {};
      $defer = $this->getMock('Carica\Io\Deferred');
      $defer
        ->expects($this->once())
        ->method('pipe')
        ->with($function, $function, $function)
        ->will($this->returnValue($promise));

      $promise = new Promise($defer);
      $filterPromise = $promise->pipe($function, $function, $function);
      $this->assertInstanceOf('Carica\Io\Deferred\Promise', $filterPromise);
      $this->assertNotSame($filterPromise, $promise);
    }

    /**
     * @covers Carica\Io\Deferred\Promise::progress
     */
    public function testProgress() {
      $function = function() {};
      $defer = $this->getMock('Carica\Io\Deferred');
      $defer
        ->expects($this->once())
        ->method('progress')
        ->with($function)
        ->will($this->returnSelf());

      $promise = new Promise($defer);
      $this->assertSame($promise, $promise->progress($function));
    }
  }
}