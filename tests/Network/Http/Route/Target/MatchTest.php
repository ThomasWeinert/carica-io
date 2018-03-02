<?php

namespace Carica\Io\Network\Http\Route\Target {

  include_once(__DIR__.'/../../../../Bootstrap.php');

  use Carica\Io\Network\Http;
  use PHPUnit\Framework\TestCase;

  class MatchTest extends TestCase {

    /**
     * @covers \Carica\Io\Network\Http\Route\Target\Match
     */
    public function testWithInvalidMethod() {
      $result = FALSE;
      $target = new Match(
        function() use (&$result) { $result = TRUE; },
        '/foo'
      );
      $target->methods('POST');
      $target($this->getRequestFixture('GET'));
      $this->assertFalse($result);
    }

    /**
     * @covers \Carica\Io\Network\Http\Route\Target\Match
     * @dataProvider provideValidPaths
     */
    public function testWithValidPaths($path, $expectedParameters) {
      $result = FALSE;
      $target = new Match(
        function(Http\Request $request, $parameters) use (&$result) {
          $result = $parameters;
          return TRUE;
        },
        $path
      );
      $this->assertTrue($target($this->getRequestFixture()));
      $this->assertEquals($expectedParameters, $result);
    }

    public static function provideValidPaths(){
      return array(
        array('/foo/bar/42', array()),
        array('/foo/bar/{id}', array('id' => 42)),
        array('/foo/{group}/{id}', array('group' => 'bar', 'id' => 42)),
        array('/foo/{group}/42', array('group' => 'bar'))
      );
    }

    /**
     * @covers \Carica\Io\Network\Http\Route\Target\Match
     * @dataProvider provideInvalidPaths
     */
    public function testWithInvalidPaths($path) {
      $result = FALSE;
      $target = new Match(
        function(Http\Request $request, $parameters) use (&$result) {
          return TRUE;
        },
        $path
      );
      $this->assertFalse($target($this->getRequestFixture()));
    }

    public static function provideInvalidPaths(){
      return array(
        'to short' => array('/foo'),
        'to long' => array('/foo/bar/42/detail'),
        'different element' => array('/foo/bar/23')
      );
    }

    private function getRequestFixture() {
      $request = $this
        ->getMockBuilder(Http\Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $request
        ->expects($this->any())
        ->method('__get')
        ->will(
          $this->returnValueMap(
            array(
              array('method', 'GET'),
              array('path', '/foo/bar/42')
            )
          )
        );
      return $request;
    }
  }
}