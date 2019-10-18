<?php

namespace Carica\Io\File\Access {

  include_once(__DIR__.'/../../Bootstrap.php');

  use Carica\Io\File;
  use PHPUnit\Framework\TestCase;

  class AggregationTest extends TestCase {

    /**
     * @covers \Carica\Io\File\Access\Aggregation
     */
    public function testGetAfterSet(): void {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->fileAccess($fileAccess = $this->createMock(File\Access::class));
      $this->assertSame($fileAccess, $aggregation->fileAccess());
    }

    /**
     * @covers \Carica\Io\File\Access\Aggregation
     */
    public function testGetImplicitCreate(): void {
      $aggregation = new Aggregation_TestProxy();
      $this->assertNotNull($aggregation->fileAccess());
    }
  }

  class Aggregation_TestProxy implements File\HasAccess {

    use Aggregation;
  }
}
