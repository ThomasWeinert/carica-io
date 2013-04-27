<?php

namespace Carica\Io\Firmata {

  include_once(__DIR__.'/../Bootstrap.php');

  class PinsTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Firmata\Pins::__construct
     */
    public function testConstructor() {
      $pins = new Pins(
        $this->getBoardFixture(),
        array(42 => array(PIN_STATE_OUTPUT))
      );
      $this->assertCount(1, $pins);
    }

    /**
     * @covers Carica\Io\Firmata\Pins::getIterator
     */
    public function testIterator() {
      $pins = new Pins(
        $board = $this->getBoardFixture(),
        array(42 => array(PIN_STATE_OUTPUT))
      );
      $this->assertEquals(
        array(42 => new Pin($board, 42, array(PIN_STATE_OUTPUT))),
        iterator_to_array($pins)
      );
    }

    /**
     * @covers Carica\Io\Firmata\Pins::offsetExists
     */
    public function testArrayAccessOffsetExistsExpectingTrue() {
      $pins = new Pins(
        $board = $this->getBoardFixture(),
        array(42 => array(PIN_STATE_OUTPUT))
      );
      $this->assertTrue(isset($pins[42]));
    }

    /**
     * @covers Carica\Io\Firmata\Pins::offsetExists
     */
    public function testArrayAccessOffsetExistsExpectingFalse() {
      $pins = new Pins(
        $board = $this->getBoardFixture(),
        array(42 => array(PIN_STATE_OUTPUT))
      );
      $this->assertFalse(isset($pins[23]));
    }

    /**
     * @covers Carica\Io\Firmata\Pins::offsetGet
     */
    public function testArrayAccessOffsetGet() {
      $pins = new Pins(
        $board = $this->getBoardFixture(),
        array(42 => array(PIN_STATE_OUTPUT))
      );
      $this->assertInstanceOf('Carica\Io\Firmata\Pin', $pins[42]);
    }

    /**
     * @covers Carica\Io\Firmata\Pins::offsetGet
     */
    public function testArrayAccessOffsetGetWithInvalidOffsetExpectingException() {
      $pins = new Pins(
        $this->getBoardFixture(), array()
      );
      $this->setExpectedException(
        'Carica\Io\Firmata\Exception\NonExistingPin'
      );
      $pins[42];
    }

    /**
     * @covers Carica\Io\Firmata\Pins::offsetSet
     */
    public function testArrayAccessOffsetSetExpectingException() {
      $pins = new Pins(
        $this->getBoardFixture(), array()
      );
      $this->setExpectedException(
        'LogicException'
      );
      $pins[] = '';
    }

    /**
     * @covers Carica\Io\Firmata\Pins::offsetUnset
     */
    public function testArrayAccessOffsetUnsetExpectingException() {
      $pins = new Pins(
        $this->getBoardFixture(), array()
      );
      $this->setExpectedException(
        'LogicException'
      );
      unset($pins[42]);
    }

    /*****************
     * Fixtures
     *****************/

    private function getBoardFixture() {
      $board = $this
        ->getMockBuilder('Carica\Io\Firmata\Board')
        ->disableOriginalConstructor()
        ->getMock();
      return $board;
    }
  }
}