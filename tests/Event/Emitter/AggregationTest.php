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