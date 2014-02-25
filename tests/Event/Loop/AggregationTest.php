<?php

namespace Carica\Io\Event\Loop {

  include_once(__DIR__.'/../../Bootstrap.php');

  class AggregationTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers \Carica\Io\Event\Loop\Aggregation
     */
    public function testGetAfterSet() {
      $aggregation = new Aggregation_TestProxy();
      $aggregation->loop($loop = $this->getMock('Carica\Io\Event\Loop'));
      $this->assertSame($loop, $aggregation->loop());
    }

    /**
     * @covers \Carica\Io\Event\Loop\Aggregation
     */
    public function testGetImplicitCreate() {
      $aggregation = new Aggregation_TestProxy();
      $this->assertInstanceOf('Carica\Io\Event\Loop', $aggregation->loop());
    }
  }

  class Aggregation_TestProxy {

    use Aggregation;
  }

}