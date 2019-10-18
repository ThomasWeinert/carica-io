<?php

namespace Carica\Io\Network\HTTP {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  class RouteTest extends TestCase {

    /**
     * @covers \Carica\Io\Network\HTTP\Route
     */
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

    /**
     * @covers \Carica\Io\Network\HTTP\Route
     */
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

    /**
     * @covers \Carica\Io\Network\HTTP\Route
     */
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

    /**
     * @covers \Carica\Io\Network\HTTP\Route
     */
    public function testRoutingOneMatchingTarget(): void {
      $request = $this
        ->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->getMock();
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

    /**
     * @covers \Carica\Io\Network\HTTP\Route
     */
    public function testRoutingNoMatchingTarget(): void {
      $request = $this
        ->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->getMock();

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
