<?php

namespace Carica\Io\Deferred {

  include_once(__DIR__.'/../Bootstrap.php');

  use Carica\Io;

  class MySQLTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Deferred\MySQL
     */
    public function testCreatingPromiseThatGetsRejected() {
      $mysql = new MySQL($this->getMySQLConnectionFixture(FALSE));
      $mysql->loop($this->getLoopFixture());
      $promise = $mysql('SQL');
      $this->assertEquals(\Carica\Io\Deferred::STATE_REJECTED, $promise->state());
    }

    /**
     * @covers Carica\Io\Deferred\MySQL
     */
    public function testCreatingPromiseThatGetsResolved() {
      $mysql = new MySQL($this->getMySQLConnectionFixture(TRUE));
      $mysql->loop($this->getLoopFixture());
      $promise = $mysql('SQL');
      $this->assertEquals(\Carica\Io\Deferred::STATE_RESOLVED, $promise->state());
    }

    public function getLoopFixture() {
      $loop = $this->getMock('Carica\Io\Event\Loop');
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
      $mysqli = $this
        ->getMockBuilder('Carica\Io\Deferred\MySQL')
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
      if ($result) {
        $mysqli
          ->expects($this->once())
          ->method('reap_async_query')
          ->will($this->returnValue($result));
      }
      return $mysqli;
    }
  }
}