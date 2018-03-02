<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;
  use PHPUnit\Framework\TestCase;

  class MySQLTest extends TestCase {

    public function setUp() {
      if (!defined('MYSQLI_ASYNC')) {
        $this->markTestSkipped('MySQL async not available in this PHP.');
      }
    }

    /**
     * @covers \Carica\Io\Deferred\MySQL
     */
    public function testCreatingPromiseThatGetsRejected() {
      $mysql = new MySQL($this->getMySQLConnectionFixture(FALSE));
      $mysql->loop($this->getLoopFixture());
      $promise = $mysql('SQL');
      $this->assertEquals(Io\Deferred::STATE_REJECTED, $promise->state());
    }

    /**
     * @covers \Carica\Io\Deferred\MySQL
     */
    public function testCreatingPromiseThatGetsResolved() {
      $mysql = new MySQL($this->getMySQLConnectionFixture(TRUE));
      $mysql->loop($this->getLoopFixture());
      $promise = $mysql('SQL');
      $this->assertEquals(Io\Deferred::STATE_RESOLVED, $promise->state());
    }

    public function getLoopFixture() {
      $loop = $this->createMock(Io\Event\Loop::class);
      $loop
        ->expects($this->once())
        ->method('setInterval')
        ->will(
          $this->returnCallback(
            function ($callback) {
              $callback();
            }
          )
        );
      return $loop;
    }

    public function getMySQLConnectionFixture($result = FALSE) {
      // seems that mysqli is not completly mockable, we use a pseudo class
      $mysqli = $this
        ->getMockBuilder('mysqli_just_for_mocking')
        ->disableOriginalConstructor()
        ->setMethods(array('query', 'poll', 'reap_async_query'))
        ->getMock();
      $mysqli
        ->expects($this->once())
        ->method('query')
        ->with('SQL', MYSQLI_ASYNC);
      $mysqli
        ->expects($this->once())
        ->method('poll')
        ->with(
          $this->isType('array'),
          $this->isType('array'),
          $this->isType('array'),
          0,
          0
        )
        ->will($this->returnValue(TRUE));
      $mysqli
        ->expects($this->once())
        ->method('reap_async_query')
        ->will($this->returnValue($result));
      return $mysqli;
    }
  }
}