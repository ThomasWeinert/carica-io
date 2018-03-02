<?php

namespace Carica\Io\File\Access {

  include_once(__DIR__.'/../../Bootstrap.php');

  use Carica\Io\File;
  use PHPUnit\Framework\TestCase;

  class AggregationTest extends TestCase {

    /**
     * @covers \Carica\Io\File\Access\Aggregation
     */
    public function testGetAfterSet() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->fileAccess($fileAccess = $this->createMock(File\Access::class));
      $this->assertSame($fileAccess, $aggregation->fileAccess());
    }

    /**
     * @covers \Carica\Io\File\Access\Aggregation
     */
    public function testGetImplicitCreate() {
      $aggregation = new Aggregation_TestProxy();
      $this->assertInstanceOf(File\Access::class, $aggregation->fileAccess());
    }
  }

  class Aggregation_TestProxy implements File\HasAccess {

    use Aggregation;
  }
}