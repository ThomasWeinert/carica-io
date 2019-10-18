<?php

namespace Carica\Io {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/Bootstrap.php');

  /**
   * @covers \Carica\Io\ByteArray
   */
  class ByteArrayTest extends TestCase {

    public function testConstructor(): void {
      $bytes = new ByteArray(3);
      $this->assertSame([0, 0, 0], iterator_to_array($bytes));
      $this->assertSame(3, $bytes->getLength());
    }

    public function testConstructorWithInvalidLengthExpectingException(): void {
      $this->expectException(\OutOfRangeException::class);
      $this->expectExceptionMessage('Zero or negative length is not possible');
      new ByteArray(-3);
    }

    public function testSetLengthIncreaseFrom3To6(): void {
      $bytes = new ByteArray(3);
      $bytes->setLength(6);
      $this->assertSame([0, 0, 0, 0, 0, 0], iterator_to_array($bytes));
      $this->assertSame(6, $bytes->getLength());
    }

    public function testSetLengthDecreaseFrom6To3(): void {
      $bytes = new ByteArray(6);
      $bytes->setLength(3);
      $this->assertSame([0, 0, 0], iterator_to_array($bytes));
      $this->assertSame(3, $bytes->getLength());
    }

    public function testSetLengthWithInvalidLengthExpectingException(): void {
      $bytes = new ByteArray(6);
      $this->expectException(\OutOfRangeException::class);
      $this->expectExceptionMessage('Zero or negative length is not possible');
      $bytes->setLength(-23);
    }

    public function testGetLength(): void {
      $bytes = new ByteArray(6);
      $this->assertEquals(6, $bytes->getLength());
    }

    /**
     * @dataProvider provideBinarySamples
     */
    public function testStringInOut($binary): void {
      $bytes = new ByteArray(strlen($binary));
      $bytes->fromString($binary);
      $this->assertSame($binary, (string)$bytes);
    }

    public function testFromStringWithInvalidLengthExpectingException(): void {
      $bytes = new ByteArray(42);
      $this->expectException(\OutOfBoundsException::class);
      $bytes->fromString(pack('C*', 255, 255, 255));
    }

    public function testFromStringWithAutomaticLengthIncrease(): void {
      $bytes = new ByteArray(1);
      $bytes->fromString($binary = pack('C*', 255, 128, 255), TRUE);
      $this->assertSame('11111111 10000000 11111111', $bytes->asBitString());
    }

    public function testFromStringWithAutomaticLengthDecrease(): void {
      $bytes = new ByteArray(10);
      $bytes->fromString($binary = pack('C*', 255, 128, 255), TRUE);
      $this->assertSame('11111111 10000000 11111111', $bytes->asBitString());
    }

    /**
     * @dataProvider provideHexSamples
     */
    public function testHexStringInOut($string, $binaryString, $length): void {
      $bytes = new ByteArray($length);
      $bytes->fromHexString($string);
      $this->assertSame($string, $bytes->asHex());
    }

    public function testFromHexStringWithInvalidLengthExpectingException(): void {
      $bytes = new ByteArray(42);
      $this->expectException(\OutOfBoundsException::class);
      $bytes->fromHexString('FFF0FF', FALSE);
    }

    public function testFromHexStringWithAutomaticLengthIncrease(): void {
      $bytes = new ByteArray(1);
      $bytes->fromHexString('FF F0 FF', TRUE);
      $this->assertSame('11111111 11110000 11111111', $bytes->asBitString());
    }

    public function testFromHexStringWithAutomaticLengthDecrease(): void {
      $bytes = new ByteArray(10);
      $bytes->fromHexString('FF F0 FF', TRUE);
      $this->assertSame('11111111 11110000 11111111', $bytes->asBitString());
    }

    public function testFromArray(): void {
      $bytes = new ByteArray(2);
      $bytes->fromArray([0x00, 0xFF]);
      $this->assertEquals('00ff', $bytes->asHex());
    }

    public function testFromArrayWithInvalidLengthExpectingException(): void {
      $bytes = new ByteArray(42);
      $this->expectException(\OutOfBoundsException::class);
      $bytes->fromArray([0xFF, 0xF0, 0xFF], FALSE);
    }

    public function testFromArrayWithAutomaticLengthIncrease(): void {
      $bytes = new ByteArray(1);
      $bytes->fromArray([0xFF, 0xF0, 0xFF], TRUE);
      $this->assertSame('11111111 11110000 11111111', $bytes->asBitString());
    }

    public function testFromArrayWithAutomaticLengthDecrease(): void {
      $bytes = new ByteArray(10);
      $bytes->fromArray([0xFF, 0xF0, 0xFF], TRUE);
      $this->assertSame('11111111 11110000 11111111', $bytes->asBitString());
    }

    /**
     * @dataProvider provideHexSamples
     */
    public function testAsHex($expected, $binaryString, $length): void {
      $bytes = new ByteArray($length);
      $bytes->fromString($binaryString);
      $this->assertSame($expected, $bytes->asHex());
    }

    /**
     * @dataProvider provideBitStringSamples
     */
    public function testAsBitString($expected, $binaryString, $length): void {
      $bytes = new ByteArray($length);
      $bytes->fromString($binaryString);
      $this->assertSame($expected, $bytes->asBitString());
    }

    public function testAsArray(): void {
      $bytes = new ByteArray();
      $bytes->fromString('Foo', TRUE);
      $this->assertEquals([70, 111, 111], $bytes->asArray());
    }

    public function testBitExistsExpectingTrue(): void {
      $bytes = new ByteArray(3);
      $this->assertTrue(isset($bytes[[2, 4]]));
    }

    public function testBitExistsExpectingFalse(): void {
      $bytes = new ByteArray(3);
      $this->assertFalse(isset($bytes[[2, 42]]));
    }

    public function testBitExistsWithInvalidOffsetExpectingException(): void {
      $bytes = new ByteArray(3);
      $this->expectException(\OutOfBoundsException::class);
      $bytes[[23, 23, 23]];
    }

    public function testByteExistsExpectingTrue(): void {
      $bytes = new ByteArray(3);
      $this->assertTrue(isset($bytes[2]));
    }

    public function testByteExistsExpectingFalse(): void {
      $bytes = new ByteArray(3);
      $this->assertFalse(isset($bytes[23]));
    }

    public function testSetLowestBit(): void {
      $bytes = new ByteArray(1);
      $bytes[[0, 0]] = TRUE;
      $this->assertSame('00000001', $bytes->asBitString());
    }

    public function testDisableLowestBit(): void {
      $bytes = new ByteArray(1);
      $bytes->fromString(pack('C*', 1));
      $bytes[[0, 0]] = FALSE;
      $this->assertSame('00000000', $bytes->asBitString());
    }

    public function testSetHighestBit(): void {
      $bytes = new ByteArray(1);
      $bytes[[0, 7]] = TRUE;
      $this->assertSame('10000000', $bytes->asBitString());
    }

    public function testDisableHighestBit(): void {
      $bytes = new ByteArray(1);
      $bytes->fromString(pack('C*', 255));
      $bytes[[0, 7]] = FALSE;
      $this->assertSame('01111111', $bytes->asBitString());
    }

    public function testUnsetLowestBit(): void {
      $bytes = new ByteArray(1);
      $bytes->fromString(pack('C*', 255));
      unset($bytes[[0, 0]]);
      $this->assertSame('11111110', $bytes->asBitString());
    }

    /**
     * @dataProvider provideBitOffsetSamples
     * @param $expected
     * @param $byte
     * @param $bit
     * @param $status
     */
    public function testBitGetAfterSet($expected, $byte, $bit, $status): void {
      $bytes = new ByteArray(2);
      $bytes->fromString(pack('C*', 0, 255));
      $bytes[[$byte, $bit]] = $status;
      $this->assertSame($status, $bytes[[$byte, $bit]]);
      $this->assertSame($expected, $bytes->asBitString());
    }

    /**
     * @dataProvider provideByteOffsetSamples
     */
    public function testByteGetAfterSet($expected, $byte, $value): void {
      $bytes = new ByteArray(2);
      $bytes->fromString(pack('C*', 0, 255));
      $bytes[$byte] = $value;
      $this->assertSame($value, $bytes[$byte]);
      $this->assertSame($expected, $bytes->asBitString());
    }

    /**
     * @dataProvider provideInvalidOffsets
     * @param $position
     */
    public function testValidateIndexExpectingException($position): void {
      $bytes = new ByteArray(2);
      $this->expectException(\OutOfBoundsException::class);
      $bytes[$position] = 1;
    }

    /**
     * @dataProvider provideInvalidValues
     */
    public function testValidateValueExpectingException($value): void {
      $bytes = new ByteArray(1);
      $this->expectException(\OutOfRangeException::class);
      $bytes[0] = $value;
    }

    public function testGetIteratorOnEmptyByteArrayWithDefinedLength(): void {
      $bytes = new ByteArray(3);
      $this->assertEquals(
        [0x00, 0x00, 0x00],
        iterator_to_array($bytes)
      );
    }

    public function testGetIteratorOnLoadedBytes(): void {
      $bytes = new ByteArray();
      $bytes->fromHexString('FFF00F', TRUE);
      $this->assertEquals(
        [0xFF, 0x0F0, 0x0F],
        iterator_to_array($bytes)
      );
    }

    public function testCountOnEmptyByteArrayWithDefinedLength(): void {
      $bytes = new ByteArray(42);
      $this->assertCount(
        42, $bytes
      );
    }

    public function testCountLoadedBytes(): void {
      $bytes = new ByteArray();
      $bytes->fromHexString('FFF00F', TRUE);
      $this->assertCount(
        3, $bytes
      );
    }

    public function testCreateFromHexString(): void {
      $bytes = ByteArray::createFromHex('FFF0F1');
      $this->assertEquals(
        "\xFF\xF0\xF1", (string)$bytes
      );
    }

    public function testCreateFromArray(): void {
      $bytes = ByteArray::createFromArray([0x00, 0xF0, 0xFF]);
      $this->assertEquals(
        "\x00\xF0\xFF", (string)$bytes
      );
    }

    /**************************
     * Data Provider
     *************************/

    public static function provideBinarySamples(): array {
      return [
        [pack('C*', 0)],
        [pack('C*', 1)],
        [pack('C*', 0, 1, 0)],
        [pack('C*', 255, 255, 255)],
      ];
    }

    public static function provideHexSamples(): array {
      return [
        ['00', pack('C*', 0), 1],
        ['0000', pack('C*', 0, 0), 2],
        ['000100', pack('C*', 0, 1, 0), 3],
        ['01', pack('C*', 1), 1],
        ['010203', pack('C*', 1, 2, 3), 3],
        ['ffffff', pack('C*', 255, 255, 255), 3]
      ];
    }

    public static function provideBitStringSamples(): array {
      return [
        ['00000000', pack('C*', 0), 1],
        ['00000001 00000010 00000011', pack('C*', 1, 2, 3), 3]
      ];
    }

    public static function provideBitOffsetSamples(): array {
      return [
        ['00000001 11111111', 0, 0, TRUE],
        ['00000000 11111111', 1, 0, TRUE],
        ['00000000 11111111', 0, 0, FALSE],
        ['00000000 11111110', 1, 0, FALSE],
        ['00010000 11111111', 0, 4, TRUE],
        ['00000000 11111111', 1, 4, TRUE],
        ['00000000 11111111', 0, 4, FALSE],
        ['00000000 11101111', 1, 4, FALSE],
        ['10000000 11111111', 0, 7, TRUE],
        ['00000000 11111111', 1, 7, TRUE],
        ['00000000 11111111', 0, 7, FALSE],
        ['00000000 01111111', 1, 7, FALSE]
      ];
    }

    public static function provideByteOffsetSamples(): array {
      return [
        ['00000000 11111111', 0, 0],
        ['00000000 00000000', 1, 0],
        ['10000000 11111111', 0, 128],
        ['00000000 10000000', 1, 128],
        ['11111111 11111111', 0, 255],
        ['00000000 11111111', 1, 255]
      ];
    }

    public static function provideInvalidOffsets(): array {
      return [
        [-1],
        [3],
        [[-1, 0]],
        [[2, -1]],
        [[2, 8]],
      ];
    }

    public static function provideInvalidValues(): array {
      return [
        [-1],
        [256]
      ];
    }
  }
}
