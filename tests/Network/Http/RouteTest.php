<?php

namespace Carica\Io\Network\HTTP {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../Bootstrap.php');

  class RouteTest extends TestCase {

    /**
     * @covers \Carica\Io\Network\HTTP\Route
     */
    public function testAny() {
      $route = new Route();
      $route->any($function = function() {});
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
    public function testMatch() {
      $route = new Route();
      $route->match('/path', $function = function() {});
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
    public function testStartsWith() {
      $route = new Route();
      $route->startsWith('/path', $function = function() {});
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
    public function testRoutingOneMatchingTarget() {
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
        function() use ($response) {
          return $response;
        }
      );
      $this->assertSame($response, $route($request));
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Route
     */
    public function testRoutingNoMatchingTarget() {
      $request = $this
        ->getMockBuilder(Request::class)
        ->disableOriginalConstructor()
        ->getMock();

      $route = new Route();
      $route->any(
        function() {
          return NULL;
        }
      );
      $this->assertNull($route($request));
    }
  }
}
