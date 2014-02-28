<?php

namespace Carica\Io\Network\Http\Route\Target {

  include_once(__DIR__.'/../../../../Bootstrap.php');

  use Carica\Io\Network\Http;

  class StartsWithTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Network\Http\Route\Target\StartsWith
     * @dataProvider provideValidPaths
     */
    public function testWithValidPaths($path, $expectedParameters) {
      $result = FALSE;
      $target = new StartsWith(
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
        array('/foo/bar', array()),
        array('/foo/{group}', array('group' => 'bar'))
      );
    }

    /**
     * @covers Carica\Io\Network\Http\Route\Target\StartsWith
     */
    public function testWithInvalidPaths() {
      $result = FALSE;
      $target = new Match(
        function(Http\Request $request, $parameters) use (&$result) {
          return TRUE;
        },
        '/bar/foo'
      );
      $this->assertFalse($target($this->getRequestFixture()));
    }

    private function getRequestFixture() {
      $request = $this
        ->getMockBuilder('Carica\\Io\\Network\\Http\\Request')
        ->disableOriginalConstructor()
        ->getMock();
      $request
        ->expects($this->any())
        ->method('__get')
        ->will(
          $this->returnValueMap(
            array(
              array('method', 'GET'),
              array('path', '/foo/bar/file.html')
            )
          )
        );
      return $request;
    }
  }
}