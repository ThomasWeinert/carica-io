<?php

namespace Carica\Io\Network\HTTP {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  class HeaderTest extends TestCase {

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testConstructor(): void {
      $header = new Header('Content-Type', 'text/plain');
      $this->assertEquals('Content-Type', $header->name);
      $this->assertEquals('text/plain', $header->value);
      $this->assertEquals(['text/plain'], (array)$header->values);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testConstructorWithListData(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals('foo/bar', $header->value);
      $this->assertEquals(['text/plain', 'foo/bar'], (array)$header->values);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testStringCastReturnLastValue(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals('foo/bar', (string)$header);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header::__isset
     * @dataProvider provideValidPropertyNames
     */
    public function testIssetPropertyExpectingTrue($property): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertTrue(isset($header->$property));
    }

    public static function provideValidPropertyNames(): array {
      return array(
        array('name'),
        array('value'),
        array('values')
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header::__isset
     */
    public function testIssetPropertyExpectingFalse(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertFalse(isset($header->invalidProperty));
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testGetSetNameProperty(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->name = 'Content-Length';
      $this->assertEquals('Content-Length', $header->name);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     * @dataProvider provideInvalidHeaderNames
     */
    public function testGetSetNamePropertyExpectingException($name): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->expectException(\UnexpectedValueException::class);
      $header->name = $name;
    }

    public static function provideInvalidHeaderNames(): array {
      return array(
        array(''),
        array('   '),
        array("\t\r\n")
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testGetSetValueProperty(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->value = 'text/html';
      $this->assertEquals('text/html', $header->value);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testGetSetValuesProperty(): void {
      $header = new Header('Content-Type', 'foo/bar');
      $header->values = ['text/plain', 'text/html'];
      $this->assertEquals(['text/plain', 'text/html'], (array)$header->values);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header::__get
     */
    public function testGetInvalidPropertyExpectingException(): void {
      $header = new Header('Content-Type', 'foo/bar');
      $this->expectException(\LogicException::class);
      $header->invalidProperty;
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header::__set
     */
    public function testSetInvalidPropertyExpectingException(): void {
      $header = new Header('Content-Type', 'foo/bar');
      $this->expectException(\LogicException::class);
      $header->invalidProperty = 'fail';
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testSetOffsetInValuesProperty(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->values[1] = 'text/html';
      $this->assertEquals(['text/plain', 'text/html'], (array)$header->values);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testOffsetExistsExpectingTrue(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertTrue(isset($header[1]));
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testOffsetExistsExpectingFalse(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertFalse(isset($header[99]));
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testOffsetGetAfterSet(): void {
      $header = new Header('Content-Type');
      $header[] = 'text/plain';
      $this->assertEquals('text/plain', $header[0]);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header
     */
    public function testOffsetUnset(): void {
      $header = new Header('Content-Type', 'text/plain');
      unset($header[0]);
      $this->assertCount(0, $header);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header::count
     */
    public function testCountExpectingZero(): void {
      $header = new Header('Content-Type');
      $this->assertCount(0, $header);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header::count
     */
    public function testCountExpectingOne(): void {
      $header = new Header('Content-Type', 'text/html');
      $this->assertCount(1, $header);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header::count
     */
    public function testCountExpectingTwo(): void {
      $header = new Header('Content-Type', ['text/plain', 'text/html']);
      $this->assertCount(2, $header);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Header::getIterator
     */
    public function testHeaderAsIterator(): void {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals(
        array('text/plain', 'foo/bar'),
        iterator_to_array($header)
      );
    }
  }
}
