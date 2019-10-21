<?php

namespace Carica\Io\File\Access {

  include_once(__DIR__.'/../../Bootstrap.php');

  use Carica\Io\File;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  /**
   * @covers \Carica\Io\File\Access\Aggregation
   */
  class AggregationTest extends TestCase {

    public function testGetAfterSet(): void {
      $aggregation = new Aggregation_TestProxy();
      /** @var MockObject|File\Access $fileAccess */
      $fileAccess = $this->createMock(File\Access::class);
      $aggregation->fileAccess($fileAccess);
      $this->assertSame($fileAccess, $aggregation->fileAccess());
    }

    public function testGetImplicitCreate(): void {
      $aggregation = new Aggregation_TestProxy();
      $this->assertNotNull($aggregation->fileAccess());
    }
  }

  class Aggregation_TestProxy implements File\HasAccess {

    use Aggregation;
  }
}
