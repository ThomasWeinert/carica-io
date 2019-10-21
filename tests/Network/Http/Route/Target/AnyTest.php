<?php

namespace Carica\Io\Network\HTTP\Route\Target {

  include_once(__DIR__.'/../../../../Bootstrap.php');

  use Carica\Io\Network\HTTP\Request;
  use InvalidArgumentException;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  /**
   * @covers \Carica\Io\Network\HTTP\Route\Target\Any
   */
  class AnyTest extends TestCase {

    public function testWithoutLimitation(): void {
      $result = FALSE;
      $target = new Any(
        static function() use (&$result) { $result = TRUE; }
      );
      $target($this->getRequestFixture());
      $this->assertTrue($result);
    }

    public function testMethodsIncludeCurrent(): void {
      $result = FALSE;
      $target = new Any(
        static function() use (&$result) { $result = TRUE; }
      );
      $target->methods('GET POST');
      $target($this->getRequestFixture('POST'));
      $this->assertTrue($result);
    }

    public function testMethodsNotIncludeCurrent(): void {
      $result = FALSE;
      $target = new Any(
        static function() use (&$result) { $result = TRUE; }
      );
      $target->methods('POST');
      $target($this->getRequestFixture('GET'));
      $this->assertFalse($result);
    }

    public function testMethodsWithInvalidValue(): void {
      $target = new Any(
        static function() { }
      );
      $this->expectException(InvalidArgumentException::class);
      $target->methods('123');
    }

    /**
     * @param string $method
     * @return MockObject|Request
     */
    private function getRequestFixture($method = 'GET') {
      $request = $this
        ->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $request
        ->expects($this->any())
        ->method('__get')
        ->with('method')
        ->willReturn($method);
      return $request;
    }
  }
}
