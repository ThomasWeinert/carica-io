<?php

namespace Carica\Io\Event\Loop {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  class AggregationTest extends TestCase {

    /**
     * @covers \Carica\Io\Event\Loop\Aggregation
     */
    public function testGetAfterSet(): void {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->loop($loop = $this->createMock(\Carica\Io\Event\Loop::class));
      $this->assertSame($loop, $aggregation->loop());
    }

    /**
     * @covers \Carica\Io\Event\Loop\Aggregation
     */
    public function testGetImplicitCreate(): void {
      $aggregation = new Aggregation_TestProxy();
      $this->assertNotNull($aggregation->loop());
    }
  }

  class Aggregation_TestProxy {

    use Aggregation;
  }

}
