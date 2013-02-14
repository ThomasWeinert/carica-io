<?php

namespace Carica\Io {

  include_once(__DIR__.'/Bootstrap.php');

  class BytesTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Firmata\ByteArray::__construct
     */
    public function testConstructor() {
      $bytes = new ByteArray(3);
      $this->assertAttributeSame(array(0,0,0), '_bytes', $bytes);
      $this->assertAttributeEquals(3, '_length', $bytes);
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::__construct
     */
    public function testConstructorWithInvalidLengthExpectingException() {
      $this->setExpectedException(
        'OutOfRangeException', 'Zero or negative length is not possible'
      );
      $bytes = new ByteArray(-3);
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::setLength
     */
    public function testSetLengthIncreaseFrom3To6() {
      $bytes = new ByteArray(3);
      $bytes->setLength(6);
      $this->assertAttributeSame(array(0,0,0,0,0,0), '_bytes', $bytes);
      $this->assertAttributeEquals(6, '_length', $bytes);
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::setLength
     */
    public function testSetLengthDecreaseFrom6To3() {
      $bytes = new ByteArray(6);
      $bytes->setLength(3);
      $this->assertAttributeSame(array(0,0,0), '_bytes', $bytes);
      $this->assertAttributeEquals(3, '_length', $bytes);
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::__toString
     * @covers Carica\Io\Firmata\ByteArray::fromString
     * @dataProvider provideBinarySamples
     */
    public function testStringInOut($binary) {
      $bytes = new ByteArray(strlen($binary));
      $bytes->fromString($binary);
      $this->assertSame($binary, (string)$bytes);
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::__toString
     * @covers Carica\Io\Firmata\ByteArray::fromString
     */
    public function testFromStringWithAutomaticLengthIncrease() {
      $bytes = new ByteArray(1);
      $bytes->fromString($binary = pack('C*', 255, 128, 255), TRUE);
      $this->assertSame('11111111 10000000 11111111', $bytes->asBitString());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::__toString
     * @covers Carica\Io\Firmata\ByteArray::fromString
     */
    public function testFromStringWithAutomaticLengthDecrease() {
      $bytes = new ByteArray(10);
      $bytes->fromString($binary = pack('C*', 255, 128, 255), TRUE);
      $this->assertSame('11111111 10000000 11111111', $bytes->asBitString());
    }


    /**
     * @covers Carica\Io\Firmata\ByteArray::asHex
     * @dataProvider provideHexSamples
     */
    public function testAsHex($expected, $binaryString, $length) {
      $bytes = new ByteArray($length);
      $bytes->fromString($binaryString);
      $this->assertSame($expected, $bytes->asHex());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::asHex
     * @dataProvider provideBitStringSamples
     */
    public function testAsBitString($expected, $binaryString, $length) {
      $bytes = new ByteArray($length);
      $bytes->fromString($binaryString);
      $this->assertSame($expected, $bytes->asBitString());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::offsetSet
     */
    public function testSetLowestBit() {
      $bytes = new ByteArray(1);
      $bytes[[0, 0]] = TRUE;
      $this->assertSame('00000001', $bytes->asBitString());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::offsetSet
     */
    public function testDisableLowestBit() {
      $bytes = new ByteArray(1);
      $bytes->fromString(pack('C*', 1));
      $bytes[[0, 0]] = FALSE;
      $this->assertSame('00000000', $bytes->asBitString());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::offsetSet
     */
    public function testSetHighestBit() {
      $bytes = new ByteArray(1);
      $bytes[[0, 7]] = TRUE;
      $this->assertSame('10000000', $bytes->asBitString());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::offsetSet
     */
    public function testDisableHighestBit() {
      $bytes = new ByteArray(1);
      $bytes->fromString(pack('C*', 255));
      $bytes[[0, 7]] = FALSE;
      $this->assertSame('01111111', $bytes->asBitString());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::offsetUnset
     */
    public function testUnsetLowestBit() {
      $bytes = new ByteArray(1);
      $bytes->fromString(pack('C*', 255));
      unset($bytes[[0, 0]]);
      $this->assertSame('11111110', $bytes->asBitString());
    }

    public static function provideBinarySamples() {
      return array(
        array(pack('C*', 0)),
        array(pack('C*', 1)),
        array(pack('C*', 0, 1, 0)),
        array(pack('C*', 255, 255, 255)),
      );
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::offsetSet
     * @covers Carica\Io\Firmata\ByteArray::offsetGet
     * @dataProvider provideBitOffsetSamples
     */
    public function testBitGetAfterSet($expected, $byte, $bit, $status) {
      $bytes = new ByteArray(2);
      $bytes->fromString(pack('C*', 0, 255));
      $bytes[[$byte, $bit]] = $status;
      $this->assertSame($status, $bytes[[$byte, $bit]]);
      $this->assertSame($expected, $bytes->asBitString());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::offsetSet
     * @covers Carica\Io\Firmata\ByteArray::offsetGet
     * @dataProvider provideByteOffsetSamples
     */
    public function testByteGetAfterSet($expected, $byte, $value) {
      $bytes = new ByteArray(2);
      $bytes->fromString(pack('C*', 0, 255));
      $bytes[$byte] = $value;
      $this->assertSame($value, $bytes[$byte]);
      $this->assertSame($expected, $bytes->asBitString());
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::validateOffset
     * @covers Carica\Io\Firmata\ByteArray::validateBitOffset
     * @dataProvider provideInvalidOffsets
     */
    public function testValidateIndexExpectingException($position) {
      $bytes = new ByteArray(2);
      $this->setExpectedException('OutOfBoundsException');
      $bytes[$position] = 1;
    }

    /**
     * @covers Carica\Io\Firmata\ByteArray::validateValue
     * @dataProvider provideInvalidValues
     */
    public function testValidateValueExpectingException($value) {
      $bytes = new ByteArray(1);
      $this->setExpectedException('OutOfRangeException');
      $bytes[0] = $value;
    }

    /**************************
     * Data Provider
     *************************/

    public static function provideHexSamples() {
      return array(
        array('00', pack('C*', 0), 1),
        array('0000', pack('C*', 0, 0), 2),
        array('000100', pack('C*', 0, 1, 0), 3),
        array('01', pack('C*', 1), 1),
        array('010203', pack('C*', 1, 2, 3), 3),
        array('ffffff', pack('C*', 255, 255, 255), 3)
      );
    }

    public static function provideBitStringSamples() {
      return array(
        array('00000000', pack('C*', 0), 1),
        array('00000001 00000010 00000011', pack('C*', 1, 2, 3), 3)
      );
    }

    public static function provideBitOffsetSamples() {
      return array(
        array('00000001 11111111', 0, 0, TRUE),
        array('00000000 11111111', 1, 0, TRUE),
        array('00000000 11111111', 0, 0, FALSE),
        array('00000000 11111110', 1, 0, FALSE),
        array('00010000 11111111', 0, 4, TRUE),
        array('00000000 11111111', 1, 4, TRUE),
        array('00000000 11111111', 0, 4, FALSE),
        array('00000000 11101111', 1, 4, FALSE),
        array('10000000 11111111', 0, 7, TRUE),
        array('00000000 11111111', 1, 7, TRUE),
        array('00000000 11111111', 0, 7, FALSE),
        array('00000000 01111111', 1, 7, FALSE)
      );
    }

    public static function provideByteOffsetSamples() {
      return array(
        array('00000000 11111111', 0, 0),
        array('00000000 00000000', 1, 0),
        array('10000000 11111111', 0, 128),
        array('00000000 10000000', 1, 128),
        array('11111111 11111111', 0, 255),
        array('00000000 11111111', 1, 255)
      );
    }

    public static function provideInvalidOffsets() {
      return array(
        array(-1),
        array(3),
        array(array(-1, 0)),
        array(array(2, -1)),
        array(array(2, 8)),
      );
    }

    public static function provideInvalidValues() {
      return array(
        array(-1),
        array(256)
      );
    }
  }
}