<?php

namespace Carica\Io\Network\Http {

  include_once(__DIR__.'/../../Bootstrap.php');

  class HeaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testConstructor() {
      $header = new Header('Content-Type', 'text/plain');
      $this->assertEquals('Content-Type', $header->name);
      $this->assertEquals('text/plain', $header->value);
      $this->assertEquals(['text/plain'], (array)$header->values);
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testConstructorWithListData() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals('foo/bar', $header->value);
      $this->assertEquals(['text/plain', 'foo/bar'], (array)$header->values);
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testStringCastReturnLastValue() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals('foo/bar', (string)$header);
    }
    
    /**
     * @covers Carica\Io\Network\Http\Header::__isset
     * @dataProvider provideValidPropertyNames
     */
    public function testIssetPropertyExpectingTrue($property) {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertTrue(isset($header->$property));
    }
    
    public static function provideValidPropertyNames() {
      return array(
        array('name'),
        array('value'),
        array('values')
      );
    }

    /**
     * @covers Carica\Io\Network\Http\Header::__isset
     */
    public function testIssetPropertyExpectingFalse() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertFalse(isset($header->invalidProperty));
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testGetSetNameProperty() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->name = 'Content-Length';
      $this->assertEquals('Content-Length', $header->name);
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     * @dataProvider provideInvalidHeaderNames
     */
    public function testGetSetNamePropertyExpectingException($name) {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->setExpectedException('UnexpectedValueException');
      $header->name = $name;
    }
    
    public static function provideInvalidHeaderNames() {
      return array(
        array(''),
        array('   '),
        array("\t\r\n")
      );
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testGetSetValueProperty() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->value = 'text/html';
      $this->assertEquals('text/html', $header->value);
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testGetSetValuesProperty() {
      $header = new Header('Content-Type', 'foo/bar');
      $header->values = ['text/plain', 'text/html'];
      $this->assertEquals(['text/plain', 'text/html'], (array)$header->values);    
    }

    /**
     * @covers Carica\Io\Network\Http\Header::__get
     */
    public function testGetInvalidPropertyExpectingException() {
      $header = new Header('Content-Type', 'foo/bar');
      $this->setExpectedException('LogicException');
      $dummy = $header->invalidProperty;
    }

    /**
     * @covers Carica\Io\Network\Http\Header::__set
     */
    public function testSetInvalidPropertyExpectingException() {
      $header = new Header('Content-Type', 'foo/bar');
      $this->setExpectedException('LogicException');
      $header->invalidProperty = 'fail';
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testSetOffsetInValuesProperty() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $header->values[1] = 'text/html';
      $this->assertEquals(['text/plain', 'text/html'], (array)$header->values);
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testOffsetExistsExpectingTrue() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertTrue(isset($header[1]));
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testOffsetExistsExpectingFalse() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertFalse(isset($header[99]));
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testOffsetGetAfterSet() {
      $header = new Header('Content-Type');
      $header[] = 'text/plain';
      $this->assertEquals('text/plain', $header[0]);
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testOffsetUnset() {
      $header = new Header('Content-Type', 'text/plain');
      unset($header[0]);
      $this->assertCount(0, $header);
    }
    
    /**
     * @covers Carica\Io\Network\Http\Header::count
     */
    public function testCountExpectingZero() {
      $header = new Header('Content-Type');
      $this->assertCount(0, $header);
    }

    /**
     * @covers Carica\Io\Network\Http\Header::count
     */
    public function testCountExpectingOne() {
      $header = new Header('Content-Type', 'text/html');
      $this->assertCount(1, $header);
    }

    /**
     * @covers Carica\Io\Network\Http\Header::count
     */
    public function testCountExpectingTwo() {
      $header = new Header('Content-Type', ['text/plain', 'text/html']);
      $this->assertCount(2, $header);
    }
    
    /**
     * @covers Carica\Io\Network\Http\Header::getIterator
     */
    public function testHeaderAsIterator() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals(
        array('text/plain', 'foo/bar'),
        iterator_to_array($header)
      );
    }
  }
}