<?php

namespace Carica\Io\Network\HTTP {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  class HeadersTest extends TestCase {

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::count
     */
    public function testCountExpectingZero() {
      $headers = new Headers();
      $this->assertCount(0, $headers);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::count
     */
    public function testCountExpectingTwo() {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $headers[] = 'Content-Length: 42';
      $this->assertCount(2, $headers);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::getIterator
     */
    public function testIteratorWithTwoElements() {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $headers[] = 'Content-Length: 42';
      $this->assertEquals(
        [
          'content-type' => new Header('Content-Type', 'the/answer'),
          'content-length' => new Header('Content-Length', 42)
        ],
        iterator_to_array($headers)
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetExists
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetExistsExpectingTrue() {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $this->assertTrue(isset($headers['Content-Type']));
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetExists
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetExistsExpectingFalse() {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $this->assertFalse(isset($headers['Content-Length']));
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetGet
     * @covers \Carica\Io\Network\HTTP\Headers::offsetSet
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetGetAfterOffsetSet() {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $this->assertEquals(
         new Header('Content-Type', 'the/answer'),
         $headers['Content-Type']
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetGet
     * @covers \Carica\Io\Network\HTTP\Headers::offsetSet
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetSetWithHeaderObject() {
      $headers = new Headers();
      $headers['Content-Type'] = $header = new Header('Content-Type', 'the/answer');
      $this->assertSame(
         $header,
         $headers['Content-Type']
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetGet
     * @covers \Carica\Io\Network\HTTP\Headers::offsetSet
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetSetWithHeaderObjectButNoName() {
      $headers = new Headers();
      $headers[] = $header = new Header('Content-Type', 'the/answer');
      $this->assertSame(
         $header,
         $headers['Content-Type']
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetGet
     * @covers \Carica\Io\Network\HTTP\Headers::offsetSet
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessSetUsingStringNameAndValue() {
      $headers = new Headers();
      $headers['Content-Type'] = 'the/answer';
      $this->assertEquals(
         new Header('Content-Type', 'the/answer'),
         $headers['Content-Type']
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetGet
     * @covers \Carica\Io\Network\HTTP\Headers::offsetSet
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessSetAddsValuesToExistingHeader() {
      $headers = new Headers();
      $headers['Set-Cookie'] = 'chocolate';
      $headers['Set-Cookie'] = 'double chocolate';
      $headers['Set-Cookie'] = 'hazlenut';
      $this->assertEquals(
         new Header('Set-Cookie', array('chocolate', 'double chocolate', 'hazlenut')),
         $headers['Set-Cookie']
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetGetWithEmptyKey() {
      $headers = new Headers();
      $this->expectException(\InvalidArgumentException::class);
      $dummy = $headers['   '];
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetGetWithInvalidKey() {
      $headers = new Headers();
      $this->expectException(\InvalidArgumentException::class);
      $dummy = $headers['123-nogood'];
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetUnset
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetUnset() {
      $headers = new Headers();
      $headers['Content-Type'] = 'the/answer';
      unset($headers['Content-Type']);
      $this->assertCount(
        0, $headers
      );
    }
  }
}
