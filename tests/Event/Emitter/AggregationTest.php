<?php

namespace Carica\Io\Event\Emitter {

  use Carica\Io\Event\Emitter;
  use InvalidArgumentException;
  use LogicException;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;
  use ReflectionClass;
  use ReflectionException;
  use UnexpectedValueException;

  include_once(__DIR__.'/../../Bootstrap.php');

  /**
   * @covers \Carica\Io\Event\Emitter\Aggregation
   */
  class AggregationTest extends TestCase {

    public function testGetEventsAfterSet(): void {
      /** @var MockObject|Aggregation_TestProxy $aggregation */
      $aggregation = new Aggregation_TestProxy();
      /** @var Emitter|MockObject $events */
      $events = $this->createMock(Emitter::class);
      $aggregation->events($events);
      $this->assertSame($events, $aggregation->events());
    }

    public function testGetEventsImplicitCreate(): void {
      $aggregation = new Aggregation_TestProxy();
      $this->assertNotNull($aggregation->events());
    }

    public function testEmitEvent(): void {
      $aggregation = new Aggregation_TestProxy();
      $result = FALSE;
      $aggregation->events()->on('test', static function() use (&$result) { $result = TRUE; });
      $aggregation->emitEvent('test');
      $this->assertTrue($result);
    }

    public function testEmitEventDoesNotImplicitCreateEmitter(): void {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->emitEvent('dummy');
      try {
        $reflection = new ReflectionClass($aggregation);
        $property = $reflection->getProperty('_eventEmitter');
        $property->setAccessible(TRUE);
        $this->assertNull($property->getValue($aggregation));
        $aggregation->events();
        $this->assertNotNull($property->getValue($aggregation));
      } catch (ReflectionException $exception) {
        $this->fail('Could not change visibility of private property.');
      }
    }

    public function testAttachEventUsingImportedMagicMethod(): void {
      /** @var MockObject|Aggregation_TestProxy $aggregation */
      $aggregation = new Aggregation_TestProxy();
      $result = FALSE;
      $aggregation->onTest(static function() use (&$result) { $result = TRUE; });
      $aggregation->emitEvent('test');
      $this->assertTrue($result);
    }

    public function testAttachEventUsingOwnMagicMethod(): void {
      /** @var MockObject|Aggregation_TestProxy $aggregation */
      $aggregation = new Aggregation_TestProxyWithCall();
      $result = FALSE;
      $aggregation->onTest(static function() use (&$result) { $result = TRUE; });
      $aggregation->events()->emit('test');
      $this->assertTrue($result);
    }

    public function testAttachInvalidEventExpectingException(): void {
      $aggregation = new Aggregation_TestProxy();
      $this->expectException(InvalidArgumentException::class);
      $this->expectExceptionMessage('No callable for event provided.');
      $aggregation->onTest(NULL);
    }

    public function testAttachInvalidMethodExpectingException(): void {
      $aggregation = new Aggregation_TestProxy();
      $this->expectException(LogicException::class);
      $this->expectExceptionMessage('Unknown method call');
      $aggregation->__call('invalid', [static function() {}]);
    }

    public function testAttachInvalidMethodSilentExpectingFalse(): void {
      $aggregation = new Aggregation_TestProxyWithCall();
      $this->assertFalse(
        $aggregation->callEmitterSilent('invalid', [static function() {}])
      );
    }

    public function testAttachFailureExpectingException(): void {
      $events = $this->createMock(Emitter::class);
      $events
        ->expects($this->once())
        ->method('on')
        ->withAnyParameters()
        ->willThrowException(new UnexpectedValueException());

      $aggregation = new Aggregation_TestProxyWithCall();
      $aggregation->events($events);
      $this->expectException(UnexpectedValueException::class);
      $aggregation->__call('onTest', [static function() {}]);
    }

    public function testAttachFailureSilentExpectingFalse(): void {
      $events = $this->createMock(Emitter::class);
      $events
        ->expects($this->once())
        ->method('on')
        ->withAnyParameters()
        ->willThrowException(new UnexpectedValueException());

      $aggregation = new Aggregation_TestProxyWithCall();
      $aggregation->events($events);
      $this->assertFalse(
        $aggregation->callEmitterSilent('onTest', [static function() {}])
      );
    }
  }

  /**
   * @method onTest(callable $listener)
   */
  class Aggregation_TestProxy {
    use Aggregation {
      Aggregation::__call as public;
      Aggregation::emitEvent as public;
    }
  }

  class Aggregation_TestProxyWithCall {
    use Aggregation;

    public function __call($method, $arguments) {
      return $this->callEmitter($method, $arguments);
    }

    public function callEmitterSilent($method, $arguments): bool {
      return $this->callEmitter($method, $arguments, TRUE);
    }
  }
}
