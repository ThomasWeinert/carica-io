<?php

namespace Carica\Io\Event\Emitter {

  include_once(__DIR__.'/../../Bootstrap.php');

  class AggregationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Event\Emitter\Aggregation::events
     */
    public function testGetEventsAfterSet() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->events($events = $this->getMock('Carica\Io\Event\Emitter'));
      $this->assertSame($events, $aggregation->events());
    }

    /**
     * @covers Carica\Io\Event\Emitter\Aggregation::events
     */
    public function testGetEventsImplicitCreate() {
      $aggregation = new Aggregation_TestProxy();
      $this->assertInstanceOf('Carica\Io\Event\Emitter', $aggregation->events());
    }

  }

  class Aggregation_TestProxy {
    use Aggregation;
  }
}