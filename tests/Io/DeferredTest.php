<?php

namespace Carica\Io {

  include_once(__DIR__.'/Bootstrap.php');

  class DeferredTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Deferred
     */
    public function testResolve() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->done(
          function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->resolve('success');
      $this->assertEquals('success', $literal);
    }
    /**
     * @covers Carica\Io\Deferred
     */
    public function testResolveTriggersDoneCallbacksOnAppend() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->resolve('success')
        ->done(
          function($text) use (&$literal) {
            $literal = $text;
          }
        );
      $this->assertEquals('success', $literal);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testResolveCallsAlwaysCallbacks() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->always(
          function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->resolve('success');
      $this->assertEquals('success', $literal);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testReject() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->fail(
          function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->reject('got error');
      $this->assertEquals('got error', $literal);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testRejectTriggersFailCallbacksOnAppend() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->reject('got error')
        ->fail(
          function($text) use (&$literal) {
            $literal = $text;
          }
        );
      $this->assertEquals('got error', $literal);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testRejectCallsAlwaysCallbacks() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->always(
          function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->reject('got error');
      $this->assertEquals('got error', $literal);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testRejectTriggersAlwaysCallbackOnRejectedObject() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->reject('got error')
        ->always(
          function($text) use (&$literal) {
            $literal = $text;
          }
        );
      $this->assertEquals('got error', $literal);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testPromise() {
      $defer = new Deferred();
      $promise = $defer->promise();
      $this->assertInstanceOf('Carica\Io\Deferred\Promise', $promise);
      $this->assertAttributeSame(
        $defer, '_defer', $promise
      );
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testPipeWithDoneFilter() {
      $defer = new Deferred();
      $filtered = $defer->pipe(
        function($value) {
          return $value * 2;
        }
      );
      $defer->resolve(5);
      $result = 'fail';
      $filtered->done(
        function ($value) use (&$result) {
          $result = '2 * 5 = '.$value;
        }
      );
      $this->assertEquals('2 * 5 = 10', $result);
    }

    public function testWhenWithOneDeferredArgumentsReturnsThisArgumentsPromise() {
      $testCase = $this;
      $promise = Deferred::when(
        $defer = new Deferred()
      );
      $this->assertSame($promise, $defer->promise());
    }

    public function testWhenWithOneArgumentThatsNotReferredReturnsResolvedPromise() {
      $result = NULL;
      $promise = Deferred::when(42)
        ->then(
          function ($argument) use (&$result) {
            $result = $argument;
          }
        );
      $this->assertEquals(42, $result);
    }

    public function testWhenWithTwoDeferredArguments() {
      $result = NULL;
      Deferred::when(
        $deferOne = new Deferred(),
        $deferTwo = new Deferred()
      )->done(
        function ($one, $two) use (&$result) {
          $result = array($one, $two);
        }
      );
      $deferOne->resolve('1.1', '1.2');
      $deferTwo->resolve('2.1');
      $this->assertEquals(
        array(array('1.1', '1.2'), array('2.1')),
        $result
      );
    }

    public function testWhenWithTwoArgumentsButOnlyOneDeferred() {
      $result = NULL;
      Deferred::when(
        42,
        $defer = new Deferred()
      )->done(
        function ($one, $two) use (&$result) {
          $result = array($one, $two);
        }
      );
      $defer->resolve(84);
      $this->assertEquals(
        array(array(42), array(84)),
        $result
      );
    }
  }
}