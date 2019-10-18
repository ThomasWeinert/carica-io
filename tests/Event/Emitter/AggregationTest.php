<?php

namespace Carica\Io\Event\Emitter {

  use Carica\Io\Event\Emitter;
  use PHPUnit\Framework\TestCase;
  use ReflectionClass;

  include_once(__DIR__.'/../../Bootstrap.php');

  /**
   * @covers \Carica\Io\Event\Emitter\Aggregation::emitEvent
   */
  class AggregationTest extends TestCase {

    public function testGetEventsAfterSet(): void {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->events($events = $this->createMock(Emitter::class));
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
      $reflection = new ReflectionClass($aggregation);
      $property = $reflection->getProperty('_eventEmitter');
      $property->setAccessible(true);
      $this->assertNull($property->getValue($aggregation));
      $aggregation->events();
      $this->assertNotNull($property->getValue($aggregation));

    }

    public function testAttachEventUsingImportedMagicMethod(): void {
      $aggregation = new Aggregation_TestProxy();
      $result = FALSE;
      $aggregation->onTest(static function() use (&$result) { $result = TRUE; });
      $aggregation->emitEvent('test');
      $this->assertTrue($result);
    }

    public function testAttachEventUsingOwnMagicMethod(): void {
      $aggregation = new Aggregation_TestProxyWithCall();
      $result = FALSE;
      $aggregation->onTest(static function() use (&$result) { $result = TRUE; });
      $aggregation->events()->emit('test');
      $this->assertTrue($result);
    }
  }

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
  }
}
