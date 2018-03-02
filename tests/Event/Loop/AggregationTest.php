<?php

namespace Carica\Io\Event\Loop {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  class AggregationTest extends TestCase {

    /**
     * @covers \Carica\Io\Event\Loop\Aggregation
     */
    public function testGetAfterSet() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->loop($loop = $this->createMock(\Carica\Io\Event\Loop::class));
      $this->assertSame($loop, $aggregation->loop());
    }

    /**
     * @covers \Carica\Io\Event\Loop\Aggregation
     */
    public function testGetImplicitCreate() {
      $aggregation = new Aggregation_TestProxy();
      $this->assertInstanceOf(\Carica\Io\Event\Loop::class, $aggregation->loop());
    }
  }

  class Aggregation_TestProxy {

    use Aggregation;
  }

}