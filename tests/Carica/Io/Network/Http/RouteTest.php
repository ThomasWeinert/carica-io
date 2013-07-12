<?php

namespace Carica\Io\Network\Http {

  include_once(__DIR__.'/../../Bootstrap.php');

  class RouteTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Network\Http\Route
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
     * @covers Carica\Io\Network\Http\Route
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
     * @covers Carica\Io\Network\Http\Route
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
     * @covers Carica\Io\Network\Http\Route
     */
    public function testRoutingOneMatchingTarget() {
      $request = $this
        ->getMockBuilder('Carica\Io\Network\Http\Request')
        ->disableOriginalConstructor()
        ->getMock();
      $response = $this
        ->getMockBuilder('Carica\Io\Network\Http\Response')
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
     * @covers Carica\Io\Network\Http\Route
     */
    public function testRoutingNoMatchingTarget() {
      $request = $this
        ->getMockBuilder('Carica\Io\Network\Http\Request')
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
