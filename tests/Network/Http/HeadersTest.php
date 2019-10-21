<?php

namespace Carica\Io\Network\HTTP {

  use InvalidArgumentException;
  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  /**
   * @covers \Carica\Io\Network\HTTP\Headers
   */
  class HeadersTest extends TestCase {

    public function testCountExpectingZero(): void {
      $headers = new Headers();
      $this->assertCount(0, $headers);
    }

    public function testCountExpectingTwo(): void {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $headers[] = 'Content-Length: 42';
      $this->assertCount(2, $headers);
    }

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

    public function testArrayAccessOffsetExistsExpectingTrue(): void {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $this->assertTrue(isset($headers['Content-Type']));
    }

    public function testArrayAccessOffsetExistsExpectingFalse(): void {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $this->assertFalse(isset($headers['Content-Length']));
    }

    public function testArrayAccessOffsetGetAfterOffsetSet(): void {
      $headers = new Headers();
      $headers[] = 'Content-Type: the/answer';
      $this->assertEquals(
         new Header('Content-Type', 'the/answer'),
         $headers['Content-Type']
      );
    }

    public function testArrayAccessOffsetSetWithHeaderObject(): void {
      $headers = new Headers();
      $headers['Content-Type'] = $header = new Header('Content-Type', 'the/answer');
      $this->assertSame(
         $header,
         $headers['Content-Type']
      );
    }

    public function testArrayAccessOffsetSetWithHeaderObjectButNoName(): void {
      $headers = new Headers();
      $headers[] = $header = new Header('Content-Type', 'the/answer');
      $this->assertSame(
         $header,
         $headers['Content-Type']
      );
    }

    public function testArrayAccessSetUsingStringNameAndValue(): void {
      $headers = new Headers();
      $headers['Content-Type'] = 'the/answer';
      $this->assertEquals(
         new Header('Content-Type', 'the/answer'),
         $headers['Content-Type']
      );
    }

    public function testArrayAccessSetAddsValuesToExistingHeader(): void {
      $headers = new Headers();
      $headers['Set-Cookie'] = 'chocolate';
      /** @noinspection SuspiciousAssignmentsInspection */
      $headers['Set-Cookie'] = 'double chocolate';
      /** @noinspection SuspiciousAssignmentsInspection */
      $headers['Set-Cookie'] = 'hazelnut';
      $this->assertEquals(
         new Header('Set-Cookie', array('chocolate', 'double chocolate', 'hazelnut')),
         $headers['Set-Cookie']
      );
    }

    public function testArrayAccessOffsetGetWithEmptyKey(): void {
      $headers = new Headers();
      $this->expectException(InvalidArgumentException::class);
      $headers['   '];
    }

    public function testArrayAccessOffsetGetWithInvalidKey(): void {
      $headers = new Headers();
      $this->expectException(InvalidArgumentException::class);
      $headers['123-no-good'];
    }

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
