<?php

namespace Carica\Io\Network\HTTP {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  class HeadersTest extends TestCase {

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::count
     */
    public function testCountExpectingZero(): void {
      $headers = new Headers();
      $this->assertCount(0, $headers);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::count
     */
    public function testCountExpectingTwo(): void {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $headers[] = 'Content-Length: 42';
      $this->assertCount(2, $headers);
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::getIterator
     */
    public function testIteratorWithTwoElements(): void {
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
    public function testArrayAccessOffsetExistsExpectingTrue(): void {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $this->assertTrue(isset($headers['Content-Type']));
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetExists
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetExistsExpectingFalse(): void {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $this->assertFalse(isset($headers['Content-Length']));
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetGet
     * @covers \Carica\Io\Network\HTTP\Headers::offsetSet
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetGetAfterOffsetSet(): void {
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
    public function testArrayAccessOffsetSetWithHeaderObject(): void {
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
    public function testArrayAccessOffsetSetWithHeaderObjectButNoName(): void {
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
    public function testArrayAccessSetUsingStringNameAndValue(): void {
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
    public function testArrayAccessSetAddsValuesToExistingHeader(): void {
      $headers = new Headers();
      $headers['Set-Cookie'] = 'chocolate';
      /** @noinspection SuspiciousAssignmentsInspection */
      $headers['Set-Cookie'] = 'double chocolate';
      /** @noinspection SuspiciousAssignmentsInspection */
      $headers['Set-Cookie'] = 'hazlenut';
      $this->assertEquals(
         new Header('Set-Cookie', array('chocolate', 'double chocolate', 'hazlenut')),
         $headers['Set-Cookie']
      );
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetGetWithEmptyKey(): void {
      $headers = new Headers();
      $this->expectException(\InvalidArgumentException::class);
      $headers['   '];
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetGetWithInvalidKey(): void {
      $headers = new Headers();
      $this->expectException(\InvalidArgumentException::class);
      $headers['123-nogood'];
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Headers::offsetUnset
     * @covers \Carica\Io\Network\HTTP\Headers::prepareKey
     */
    public function testArrayAccessOffsetUnset(): void {
      $headers = new Headers();
      $headers['Content-Type'] = 'the/answer';
      unset($headers['Content-Type']);
      $this->assertCount(
        0, $headers
      );
    }
  }
}
