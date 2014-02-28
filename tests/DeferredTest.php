<?php

namespace Carica\Io {

  include_once(__DIR__.'/Bootstrap.php');

  class DeferredTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Deferred
     */
    public function testStaticFunctionCreate() {
      $defer = Deferred::create();
      $this->assertInstanceOf('Carica\\Io\\Deferred', $defer);
    }

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
    public function testNotifyTriggersProgressCallback() {
      $calls = array();
      $defer = new Deferred();
      $defer
        ->progress(
          function() use (&$calls) {
            $calls[] = func_get_args();
          }
        )
        ->notify(1)
        ->notify(2, 3);
      $this->assertEquals(
        array(
          array(1),
          array(2, 3),
        ),
        $calls
      );
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testProgressCallbackIsCalledWithStoredNotify() {
      $calls = array();
      $defer = new Deferred();
      $defer
        ->notify(1)
        ->progress(
          function() use (&$calls) {
            $calls[] = func_get_args();
          }
        )
        ->notify(2, 3);
      $this->assertEquals(
        array(
          array(1),
          array(2, 3),
        ),
        $calls
      );
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testIsResolvedExpectingTrue() {
      $defer = new Deferred();
      $defer->resolve();
      $this->assertTrue($defer->isResolved());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testIsResolvedExpectingFalse() {
      $defer = new Deferred();
      $defer->reject();
      $this->assertFalse($defer->isResolved());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testIsRejectedExpectingTrue() {
      $defer = new Deferred();
      $defer->reject();
      $this->assertTrue($defer->isRejected());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testIsRejectedExpectingFalse() {
      $defer = new Deferred();
      $defer->resolve();
      $this->assertFalse($defer->isRejected());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testStateExpectingPending() {
      $defer = new Deferred();
      $this->assertEquals(Deferred::STATE_PENDING, $defer->state());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testStateExpectingResolved() {
      $defer = new Deferred();
      $defer->resolve();
      $this->assertEquals(Deferred::STATE_RESOLVED, $defer->state());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testStateExpectingRejected() {
      $defer = new Deferred();
      $defer->reject();
      $this->assertEquals(Deferred::STATE_REJECTED, $defer->state());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testPromise() {
      $defer = new Deferred();
      $promise = $defer->promise();
      $this->assertInstanceOf('Carica\\Io\\Deferred\\Promise', $promise);
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

    /**
     * @covers Carica\Io\Deferred
     */
    public function testPipeWithoutDoneFilter() {
      $defer = new Deferred();
      $filtered = $defer->pipe();
      $calls = array();
      $filtered->done(
        function ($value) use (&$calls) {
          $calls[] = $value;
        }
      );
      $defer->resolve(5);
      $this->assertEquals(array(5), $calls);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testPipeWithFailFilter() {
      $defer = new Deferred();
      $filtered = $defer->pipe(
        NULL,
        function($value) {
          return $value * 2;
        }
      );
      $defer->reject(5);
      $result = 'fail';
      $filtered->fail(
        function ($value) use (&$result) {
          $result = '2 * 5 = '.$value;
        }
      );
      $this->assertEquals('2 * 5 = 10', $result);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testPipeWithoutFailFilter() {
      $defer = new Deferred();
      $filtered = $defer->pipe();
      $calls = array();
      $filtered->fail(
          function ($value) use (&$calls) {
            $calls[] = $value;
          }
      );
      $defer->reject(5);
      $this->assertEquals(array(5), $calls);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testPipeWithNotifyFilter() {
      $calls = array();
      $defer = new Deferred();
      $defer
        ->pipe(
          NULL,
          NULL,
          function() {
            return array_sum(func_get_args());
          }
        )
        ->progress(
          function($sum) use (&$calls) {
            $calls[] = $sum;
          }
        );
      $defer->notify(1, 2, 4);
      $this->assertSame(
        array(7),
        $calls
      );
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testPipeWithoutNotifyFilter() {
      $calls = array();
      $defer = new Deferred();
      $defer
        ->pipe()
        ->progress(
          function() use (&$calls) {
            $calls[] = func_get_args();
          }
        );
      $defer->notify(1, 2, 4);
      $this->assertSame(
        array(array(1, 2, 4)),
        $calls
      );
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testWhenWithOnePromiseReturnsThisArgument() {
      $defer = new Deferred();
      $promise = $defer->promise();
      $this->assertSame(
        $promise, Deferred::when($promise)
      );
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testWhenWithOneDeferredArgumentsReturnsThisArgumentsPromise() {
      $promise = Deferred::when(
        $defer = new Deferred()
      );
      $this->assertSame($promise, $defer->promise());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testWhenWithOneArgumentThatsNotReferredReturnsResolvedPromise() {
      $result = NULL;
      Deferred::when(42)
        ->then(
          function ($argument) use (&$result) {
            $result = $argument;
          }
        );
      $this->assertEquals(42, $result);
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testWhenWithOneArgumentThatsAnArrayOfCallbacks() {
      $result = array();
      Deferred::when(42)
        ->then(
          array(
            function ($argument) use (&$result) {
              $result['first'] = $argument;
            },
            function ($argument) use (&$result) {
              $result['second'] = $argument;
            }
          )
        );
      $this->assertEquals(
        array('first' => 42, 'second' => 42),
        $result
      );
    }

    /**
     * @covers Carica\Io\Deferred
     */
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

    /**
     * @covers Carica\Io\Deferred
     */
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

    /**
     * @covers Carica\Io\Deferred
     */
    public function testWhenWithRejectedDefer() {
      $calls = array();
      $defer = new Deferred();
      $defer->reject(42, 'rejected');
      Deferred::when(
        42,
        $defer
      )->fail(
        function () use (&$calls) {
          $calls[] = func_get_args();
        }
      );
      $this->assertEquals(
        array(
          array(42, 'rejected')
        ),
        $calls
      );
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testWhenWihtoutArgumentsReturnsResolvedPromise() {
      $promise = Deferred::when();
      $this->assertInstanceOf('Carica\\Io\\Deferred\\Promise', $promise);
      $this->assertEquals(Deferred::STATE_RESOLVED, $promise->state());
    }

    /**
     * @covers Carica\Io\Deferred
     */
    public function testWhenWithSeveralScalarArgumentsReturnsResolvedPromise() {
      $calls = array();
      $promise = Deferred::when('foo', 'bar', '42');
      $promise
        ->done(
          function() use (&$calls) {
            $calls[] = func_get_args();
          }
        );
      $this->assertInstanceOf('Carica\\Io\\Deferred\\Promise', $promise);
      $this->assertEquals(Deferred::STATE_RESOLVED, $promise->state());
      $this->assertEquals(
        array(
          array(array('foo'), array('bar'), array('42'))
        ),
        $calls
      );
    }
  }
}