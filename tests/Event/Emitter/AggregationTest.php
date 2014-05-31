<?php

namespace Carica\Io\Event\Emitter {

  include_once(__DIR__.'/../../Bootstrap.php');

  class AggregationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Event\Emitter\Aggregation::events
     */
    public function testGetEventsAfterSet() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->events($events = $this->getMock('Carica\\Io\\Event\\Emitter'));
      $this->assertSame($events, $aggregation->events());
    }

    /**
     * @covers Carica\Io\Event\Emitter\Aggregation::events
     */
    public function testGetEventsImplicitCreate() {
      $aggregation = new Aggregation_TestProxy();
      $this->assertInstanceOf('Carica\\Io\\Event\\Emitter', $aggregation->events());
    }

    /**
     * @covers Carica\Io\Event\Emitter\Aggregation::emitEvent
     */
    public function testEmitEvent() {
      $aggregation = new Aggregation_TestProxy();
      $result = FALSE;
      $aggregation->events()->on('test', function() use (&$result) { $result = TRUE; });
      $aggregation->emitEvent('test');
      $this->assertTrue($result);
    }

    /**
     * @covers Carica\Io\Event\Emitter\Aggregation::emitEvent
     */
    public function testEmitEventDoesNotImplicitCreateEmitter() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->emitEvent('dummy');
      $this->assertAttributeSame(NULL, '_eventEmitter', $aggregation);
    }

    public function testAttachEventUsingMagicMethod() {
      $aggregation = new Aggregation_TestProxy();
      $result = FALSE;
      $aggregation->onTest(function() use (&$result) { $result = TRUE; });
      $aggregation->emitEvent('test');
      $this->assertTrue($result);
    }
  }

  class Aggregation_TestProxy {
    use Aggregation {
      Aggregation::callEmitter as protected;
      Aggregation::emitEvent as public;
    }

    public function __call($method, $arguments) {
      $this->callEmitter($method, $arguments);
    }
  }
}