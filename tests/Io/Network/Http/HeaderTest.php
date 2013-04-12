<?php

namespace Carica\Io\Network\Http {

  include_once(__DIR__.'/../../Bootstrap.php');

  class HeaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testConstructor() {
      $header = new Header('Content-Type', 'text/plain');
      $this->assertEquals('text/plain', (string)$header);
    }

    /**
     * @covers Carica\Io\Network\Http\Header
     */
    public function testConstructorWithListData() {
      $header = new Header('Content-Type', ['text/plain', 'foo/bar']);
      $this->assertEquals('foo/bar', (string)$header);
    }
    
    /**
     * @covers Carica\Io\Network\Http\Header
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