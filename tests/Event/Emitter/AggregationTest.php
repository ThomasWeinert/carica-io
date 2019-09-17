<?php

namespace Carica\Io\Event\Emitter {

  use Carica\Io\Event\Emitter;
  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  class AggregationTest extends TestCase {

    /**
     * @covers \Carica\Io\Event\Emitter\Aggregation::events
     */
    public function testGetEventsAfterSet() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->events($events = $this->createMock(Emitter::class));
      $this->assertSame($events, $aggregation->events());
    }

    /**
     * @covers \Carica\Io\Event\Emitter\Aggregation::events
     */
    public function testGetEventsImplicitCreate() {
      $aggregation = new Aggregation_TestProxy();
      $this->assertInstanceOf(Emitter::class, $aggregation->events());
    }

    /**
     * @covers \Carica\Io\Event\Emitter\Aggregation::emitEvent
     */
    public function testEmitEvent() {
      $aggregation = new Aggregation_TestProxy();
      $result = FALSE;
      $aggregation->events()->on('test', function() use (&$result) { $result = TRUE; });
      $aggregation->emitEvent('test');
      $this->assertTrue($result);
    }

    /**
     * @covers \Carica\Io\Event\Emitter\Aggregation::emitEvent
     */
    public function testEmitEventDoesNotImplicitCreateEmitter() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->emitEvent('dummy');
      $reflection = new \ReflectionClass($aggregation);
      $property = $reflection->getProperty('_eventEmitter');
      $property->setAccessible(true);
      $this->assertNull($property->getValue($aggregation));
      $aggregation->events();
      $this->assertNotNull($property->getValue($aggregation));

    }

    public function testAttachEventUsingImportedMagicMethod() {
      $aggregation = new Aggregation_TestProxy();
      $result = FALSE;
      $aggregation->onTest(function() use (&$result) { $result = TRUE; });
      $aggregation->emitEvent('test');
      $this->assertTrue($result);
    }

    public function testAttachEventUsingOwnMagicMethod() {
      $aggregation = new Aggregation_TestProxyWithCall();
      $result = FALSE;
      $aggregation->onTest(function() use (&$result) { $result = TRUE; });
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
