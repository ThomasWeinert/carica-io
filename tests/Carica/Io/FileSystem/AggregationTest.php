<?php

namespace Carica\Io\FileSystem {

  include_once(__DIR__.'/../Bootstrap.php');

  class AggregationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers \Carica\Io\FileSystem\Aggregation
     */
    public function testGetAfterSet() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->fileSystem($fileSystem = $this->getMock('Carica\Io\FileSystem'));
      $this->assertSame($fileSystem, $aggregation->fileSystem());
    }

    /**
     * @covers \Carica\Io\FileSystem\Aggregation
     */
    public function testGetImplicitCreate() {
      $aggregation = new Aggregation_TestProxy();
      $this->assertInstanceOf('Carica\Io\FileSystem', $aggregation->fileSystem());
    }
  }

  class Aggregation_TestProxy {

    use Aggregation;
  }
}