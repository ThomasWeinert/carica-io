<?php

namespace Carica\Io\File\Access {

  include_once(__DIR__.'/../../Bootstrap.php');

  use Carica\Io\File;

  class AggregationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers \Carica\Io\File\Access\Aggregation
     */
    public function testGetAfterSet() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->fileAccess($fileAccess = $this->getMock('Carica\Io\File\Access'));
      $this->assertSame($fileAccess, $aggregation->fileAccess());
    }

    /**
     * @covers \Carica\Io\File\Access\Aggregation
     */
    public function testGetImplicitCreate() {
      $aggregation = new Aggregation_TestProxy();
      $this->assertInstanceOf('Carica\Io\File\Access', $aggregation->fileAccess());
    }
  }

  class Aggregation_TestProxy implements File\HasAccess {

    use Aggregation;
  }
}