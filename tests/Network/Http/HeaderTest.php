<?php

namespace Carica\Io\Network\HTTP {

  use LogicException;
  use PHPUnit\Framework\TestCase;
  use UnexpectedValueException;

  include_once(__DIR__.'/../../Bootstrap.php');

  /**
   * @covers \Carica\Io\Network\HTTP\Header
   */
  class HeaderTest extends TestCase {

    public function testConstructor(): void {
      $header = new Header('Content-Type', 'text/plain');
      $this->assertEquals('Content-Type', $header->name);
      $this->assertEquals('text/plain', $header->value);
      $this->assertEquals(['text/plain'], (array)$header->values);
    }

    public function testConstructorWithListData(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals('foo/bar', $header->value);
      $this->assertEquals(['text/plain', 'foo/bar'], (array)$header->values);
    }

    public function testStringCastReturnLastValue(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals('foo/bar', (string)$header);
    }

    /**
     * @dataProvider provideValidPropertyNames
     * @param string $property
     */
    public function testIssetPropertyExpectingTrue($property): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertTrue(isset($header->$property));
    }

    public static function provideValidPropertyNames(): array {
      return [
        ['name'],
        ['value'],
        ['values']
      ];
    }

    public function testIssetPropertyExpectingFalse(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertFalse(isset($header->invalidProperty));
    }

    public function testGetSetNameProperty(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->name = 'Content-Length';
      $this->assertEquals('Content-Length', $header->name);
    }

    /**
     * @dataProvider provideInvalidHeaderNames
     * @param string $name
     */
    public function testGetSetNamePropertyExpectingException($name): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->expectException(UnexpectedValueException::class);
      $header->name = $name;
    }

    public static function provideInvalidHeaderNames(): array {
      return [
        [''],
        ['   '],
        ["\t\r\n"]
      ];
    }

    public function testGetSetValueProperty(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->value = 'text/html';
      $this->assertEquals('text/html', $header->value);
    }

    public function testGetSetValuesProperty(): void {
      $header = new Header('Content-Type', 'foo/bar');
      $header->values = ['text/plain', 'text/html'];
      $this->assertEquals(['text/plain', 'text/html'], (array)$header->values);
    }

    public function testGetInvalidPropertyExpectingException(): void {
      $header = new Header('Content-Type', 'foo/bar');
      $this->expectException(LogicException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $header->invalidProperty;
    }

    public function testSetInvalidPropertyExpectingException(): void {
      $header = new Header('Content-Type', 'foo/bar');
      $this->expectException(LogicException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $header->invalidProperty = 'fail';
    }

    public function testSetOffsetInValuesProperty(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->values[1] = 'text/html';
      $this->assertEquals(['text/plain', 'text/html'], (array)$header->values);
    }

    public function testOffsetExistsExpectingTrue(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertTrue(isset($header[1]));
    }

    public function testOffsetExistsExpectingFalse(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertFalse(isset($header[99]));
    }

    public function testOffsetGetAfterSet(): void {
      $header = new Header('Content-Type');
      $header[] = 'text/plain';
      $this->assertEquals('text/plain', $header[0]);
    }

    public function testOffsetUnset(): void {
      $header = new Header('Content-Type', 'text/plain');
      unset($header[0]);
      $this->assertCount(0, $header);
    }

    public function testCountExpectingZero(): void {
      $header = new Header('Content-Type');
      $this->assertCount(0, $header);
    }

    public function testCountExpectingOne(): void {
      $header = new Header('Content-Type', 'text/html');
      $this->assertCount(1, $header);
    }

    public function testCountExpectingTwo(): void {
      $header = new Header('Content-Type', ['text/plain', 'text/html']);
      $this->assertCount(2, $header);
    }

    public function testHeaderAsIterator(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals(
        ['text/plain', 'foo/bar'],
        iterator_to_array($header)
      );
    }
  }
}
