<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;
  use Carica\Io\Event\Loop\Listener as EventLoopListener;
  use mysqli;
  use PHPUnit\Framework\MockObject\MockObject;
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

    public function testCreatingPromiseThatGetsRejected(): void {
      $mysql = new MySQL(
        $this->getLoopFixture(),
        $this->getMySQLConnectionFixture(FALSE)
      );
      $promise = $mysql('SQL');
      $this->assertEquals(Io\Deferred::STATE_REJECTED, $promise->state());
    }

    public function testCreatingPromiseThatGetsResolved(): void {
      $mysql = new MySQL(
        $this->getLoopFixture(),
        $this->getMySQLConnectionFixture(TRUE)
      );
      $promise = $mysql('SQL');
      $this->assertEquals(Io\Deferred::STATE_RESOLVED, $promise->state());
    }

    /**
     * @return Io\Event\Loop|MockObject
     */
    public function getLoopFixture() {
      $loop = $this->createMock(Io\Event\Loop::class);
      $loop
        ->expects($this->once())
        ->method('setInterval')
        ->willReturnCallback(
          function ($callback) {
            $callback();
            return $this->createMock(EventLoopListener::class);
          }
        );
      return $loop;
    }

    /**
     * @param bool $result
     * @return mysqli|Io\Event\Loop|MockObject
     */
    public function getMySQLConnectionFixture($result = FALSE) {
      // seems that mysqli can not be mocked completely, so we use a interface
      $mysqli = $this->createMock(mysqli_just_for_mocking::class);
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

  interface mysqli_just_for_mocking {

    public function query(...$arguments);
    public function poll(...$arguments);
    public function reap_async_query(...$arguments);
  }
}
