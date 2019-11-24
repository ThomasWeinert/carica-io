<?php

namespace Carica\Io\Network\HTTP {

  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  /**
   * @covers \Carica\Io\Network\HTTP\Route
   */
  class RouteTest extends TestCase {

    public function testAny(): void {
      $route = new Route();
      $route->any($function = static function() {});
      $this->assertEquals(
        array(
          new Route\Target\Any($function)
        ),
        iterator_to_array($route)
      );
    }

    public function testMatch(): void {
      $route = new Route();
      $route->match('/path', $function = static function() {});
      $this->assertEquals(
        array(
          new Route\Target\Match($function, '/path')
        ),
        iterator_to_array($route)
      );
    }

    public function testStartsWith(): void {
      $route = new Route();
      $route->startsWith('/path', $function = static function() {});
      $this->assertEquals(
        array(
          new Route\Target\StartsWith($function, '/path')
        ),
        iterator_to_array($route)
      );
    }

    public function testRoutingOneMatchingTarget(): void {
      /** @var MockObject|Request $request */
      $request = $this
        ->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $request
        ->method('__get')
        ->willReturnMap(
          [
            ['method', 'get']
          ]
        );
      $response = $this
        ->getMockBuilder(Response::class)
        ->disableOriginalConstructor()
        ->getMock();

      $route = new Route();
      $route->any(
        static function() use ($response) {
          return $response;
        }
      );
      $this->assertSame($response, $route($request));
    }

    public function testRoutingNoMatchingTarget(): void {
      /** @var MockObject|Request $request */
      $request = $this
        ->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $request
        ->method('__get')
        ->willReturnMap(
          [
            ['method', 'get']
          ]
        );

      $route = new Route();
      $route->any(
        static function() {
          return NULL;
        }
      );
      $this->assertNull($route($request));
    }
  }
}
