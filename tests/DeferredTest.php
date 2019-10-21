<?php
declare(strict_types=1);

namespace Carica\Io {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/Bootstrap.php');

  /**
   * @covers \Carica\Io\Deferred
   */
  class DeferredTest extends TestCase {

    public function testStaticFunctionCreate(): void {
      $defer = Deferred::create();
      $this->assertNotNull($defer);
    }

    public function testResolve(): void {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->done(
          static function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->resolve('success');
      $this->assertEquals('success', $literal);
    }

    public function testResolveTriggersDoneCallbacksOnAppend(): void {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->resolve('success')
        ->done(
          static function($text) use (&$literal) {
            $literal = $text;
          }
        );
      $this->assertEquals('success', $literal);
    }

    public function testResolveCallsAlwaysCallbacks(): void {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->always(
          static function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->resolve('success');
      $this->assertEquals('success', $literal);
    }

    public function testReject(): void {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->fail(
          static function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->reject('got error');
      $this->assertEquals('got error', $literal);
    }

    public function testRejectTriggersFailCallbacksOnAppend(): void {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->reject('got error')
        ->fail(
          static function($text) use (&$literal) {
            $literal = $text;
          }
        );
      $this->assertEquals('got error', $literal);
    }

    public function testRejectCallsAlwaysCallbacks(): void {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->always(
          static function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->reject('got error');
      $this->assertEquals('got error', $literal);
    }

    public function testRejectTriggersAlwaysCallbackOnRejectedObject(): void {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->reject('got error')
        ->always(
          static function($text) use (&$literal) {
            $literal = $text;
          }
        );
      $this->assertEquals('got error', $literal);
    }

    public function testNotifyTriggersProgressCallback(): void {
      $calls = array();
      $defer = new Deferred();
      $defer
        ->progress(
          static function(...$arguments) use (&$calls) {
            $calls[] = $arguments;
          }
        );
      $defer
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

    public function testProgressCallbackIsCalledWithStoredNotify(): void {
      $calls = array();
      $defer = new Deferred();
      $defer
        ->notify(1)
        ->progress(
          static function(...$arguments) use (&$calls) {
            $calls[] = $arguments;
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

    public function testIsResolvedExpectingTrue(): void {
      $defer = new Deferred();
      $defer->resolve();
      $this->assertTrue($defer->isResolved());
    }

    public function testIsResolvedExpectingFalse(): void {
      $defer = new Deferred();
      $defer->reject();
      $this->assertFalse($defer->isResolved());
    }

    public function testIsRejectedExpectingTrue(): void {
      $defer = new Deferred();
      $defer->reject();
      $this->assertTrue($defer->isRejected());
    }

    public function testIsRejectedExpectingFalse(): void {
      $defer = new Deferred();
      $defer->resolve();
      $this->assertFalse($defer->isRejected());
    }

    public function testIsPending(): void {
      $defer = new Deferred();
      $this->assertTrue($defer->isPending());
      $defer->reject();
      $this->assertFalse($defer->isPending());
    }

    public function testStateExpectingPending(): void {
      $defer = new Deferred();
      $this->assertEquals(Deferred::STATE_PENDING, $defer->state());
    }

    public function testStateExpectingResolved(): void {
      $defer = new Deferred();
      $defer->resolve();
      $this->assertEquals(Deferred::STATE_RESOLVED, $defer->state());
    }

    public function testStateExpectingRejected(): void {
      $defer = new Deferred();
      $defer->reject();
      $this->assertEquals(Deferred::STATE_REJECTED, $defer->state());
    }

    public function testThenWithDoneFilter(): void {
      $defer = new Deferred();
      $filtered = $defer->then(
        static function($value) {
          return $value * 2;
        }
      );
      $defer->resolve(5);
      $result = 'fail';
      $filtered->done(
        static function ($value) use (&$result) {
          $result = '2 * 5 = '.$value;
        }
      );
      $this->assertEquals('2 * 5 = 10', $result);
    }

    public function testThenWithoutDoneFilter(): void {
      $defer = new Deferred();
      $filtered = $defer->then();
      $calls = array();
      $filtered->done(
        static function ($value) use (&$calls) {
          $calls[] = $value;
        }
      );
      $defer->resolve(5);
      $this->assertEquals(array(5), $calls);
    }

    public function testThenWithFailFilter(): void {
      $defer = new Deferred();
      $filtered = $defer->then(
        NULL,
        static function($value) {
          return $value * 2;
        }
      );
      $defer->reject(5);
      $result = 'fail';
      $filtered->fail(
        static function ($value) use (&$result) {
          $result = '2 * 5 = '.$value;
        }
      );
      $this->assertEquals('2 * 5 = 10', $result);
    }

    public function testThenWithoutFailFilter(): void {
      $defer = new Deferred();
      $filtered = $defer->then();
      $calls = array();
      $filtered->fail(
          static function ($value) use (&$calls) {
            $calls[] = $value;
          }
      );
      $defer->reject(5);
      $this->assertEquals(array(5), $calls);
    }

    public function testThenWithNotifyFilter(): void {
      $calls = array();
      $defer = new Deferred();
      $defer
        ->then(
          NULL,
          NULL,
          static function(...$arguments) {
            return array_sum($arguments);
          }
        )
        ->progress(
          static function($sum) use (&$calls) {
            $calls[] = $sum;
          }
        );
      $defer->notify(1, 2, 4);
      $this->assertSame(
        array(7),
        $calls
      );
    }

    public function testThenWithoutNotifyFilter(): void {
      $calls = array();
      $defer = new Deferred();
      $defer
        ->then()
        ->progress(
          static function(...$arguments) use (&$calls) {
            $calls[] = $arguments;
          }
        );
      $defer->notify(1, 2, 4);
      $this->assertSame(
        array(array(1, 2, 4)),
        $calls
      );
    }

    public function testWhenWithOnePromiseReturnsThisArgument(): void {
      $defer = new Deferred();
      $promise = $defer->promise();
      $this->assertSame(
        $promise, Deferred::when($promise)
      );
    }

    public function testWhenWithOneDeferredArgumentsReturnsThisArgumentsPromise(): void {
      $promise = Deferred::when(
        $defer = new Deferred()
      );
      $this->assertSame($promise, $defer->promise());
    }

    public function testWhenWithTwoDeferredArguments(): void {
      $result = NULL;
      Deferred::when(
        $deferOne = new Deferred(),
        $deferTwo = new Deferred()
      )->done(
        static function ($one, $two) use (&$result) {
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

    public function testWhenWithTwoArgumentsButOnlyOneDeferred(): void {
      $result = NULL;
      Deferred::when(
        42,
        $defer = new Deferred()
      )->done(
        static function ($one, $two) use (&$result) {
          $result = array($one, $two);
        }
      );
      $defer->resolve(84);
      $this->assertEquals(
        array(array(42), array(84)),
        $result
      );
    }

    public function testWhenWithRejectedDefer(): void {
      $calls = array();
      $defer = new Deferred();
      $defer->reject(42, 'rejected');
      Deferred::when(
        42,
        $defer
      )->fail(
        static function (...$arguments) use (&$calls) {
          $calls[] = $arguments;
        }
      );
      $this->assertEquals(
        array(
          array(42, 'rejected')
        ),
        $calls
      );
    }

    public function testWhenWithoutArgumentsReturnsResolvedPromise(): void {
      $promise = Deferred::when();
      $this->assertInstanceOf(Deferred\Promise::class, $promise);
      $this->assertEquals(Deferred::STATE_RESOLVED, $promise->state());
    }

    public function testWhenWithSeveralScalarArgumentsReturnsResolvedPromise(): void {
      $calls = array();
      $promise = Deferred::when('foo', 'bar', '42');
      $promise
        ->done(
          static function(...$arguments) use (&$calls) {
            $calls[] = $arguments;
          }
        );
      $this->assertInstanceOf(Deferred\Promise::class, $promise);
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
