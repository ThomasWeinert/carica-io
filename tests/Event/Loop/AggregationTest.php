<?php

namespace Carica\Io\Event\Loop {

  use Carica\Io\Event\Loop;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  /**
   * @covers \Carica\Io\Event\Loop\Aggregation
   */
  class AggregationTest extends TestCase {

    public function testGetAfterSet(): void {
      $aggregation = new Aggregation_TestProxy();
      /** @var Loop|MockObject $loop */
      $loop = $this->createMock(Loop::class);
      $aggregation->loop($loop);
      $this->assertSame($loop, $aggregation->loop());
    }

    public function testGetImplicitCreate(): void {
      $aggregation = new Aggregation_TestProxy();
      $this->assertNotNull($aggregation->loop());
    }
  }

  class Aggregation_TestProxy {

    use Aggregation;
  }

}
