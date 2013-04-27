<?php

namespace Carica\Io\Firmata {

  include_once(__DIR__.'/../Bootstrap.php');

  class BoardTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Firmata\Board::__construct
     * @covers Carica\Io\Firmata\Board::stream
     */
    public function testConstructor() {
      $board = new Board($stream = $this->getMock('Carica\Io\Stream'));
      $this->assertSame($stream, $board->stream());
    }

    /**
     * @covers Carica\Io\Firmata\Board::isActive
     */
    public function testIsActiveExpectingFalse() {
      $board = new Board($this->getMock('Carica\Io\Stream'));
      $this->assertFalse($board->isActive());
    }
  }
}