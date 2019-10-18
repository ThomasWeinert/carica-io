<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;
  use phpDocumentor\Reflection\Types\This;
  use PHPUnit\Framework\TestCase;

  /**
   * @covers \Carica\Io\Deferred\MySQL
   */
  class MySQLTest extends TestCase {

    public function setUp(): void {
      if (!defined('MYSQLI_ASYNC')) {
        $this->markTestSkipped('MySQL async not available in this PHP.');
      }
    }

    public function testCreatingPromiseThatGetsRejected() {
      $mysql = new MySQL(
        $this->getLoopFixture(),
        $this->getMySQLConnectionFixture(FALSE)
      );
      $promise = $mysql('SQL');
      $this->assertEquals(Io\Deferred::STATE_REJECTED, $promise->state());
    }

    public function testCreatingPromiseThatGetsResolved() {
      $mysql = new MySQL(
        $this->getLoopFixture(),
        $this->getMySQLConnectionFixture(TRUE)
      );
      $promise = $mysql('SQL');
      $this->assertEquals(Io\Deferred::STATE_RESOLVED, $promise->state());
    }

    public function getLoopFixture() {
      $loop = $this->createMock(Io\Event\Loop::class);
      $loop
        ->expects($this->once())
        ->method('setInterval')
        ->willReturnCallback(
          function ($callback) {
            $callback();
            return $this->createMock(\Carica\Io\Event\Loop\Listener::class);
          }
        );
      return $loop;
    }

    public function getMySQLConnectionFixture($result = FALSE) {
      // seems that mysqli is not completely mockable, we use a pseudo class
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
        ->willReturn(TRUE);
      $mysqli
        ->expects($this->once())
        ->method('reap_async_query')
        ->willReturn($result);
      return $mysqli;
    }
  }
}
