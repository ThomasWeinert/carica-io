<?php

namespace Carica\Io\Network\HTTP\Route\Target {

  include_once(__DIR__.'/../../../../Bootstrap.php');

  use Carica\Io\Network\HTTP;
  use PHPUnit\Framework\TestCase;

  class StartsWithTest extends TestCase {

    /**
     * @covers \Carica\Io\Network\HTTP\Route\Target\StartsWith
     * @dataProvider provideValidPaths
     */
    public function testWithValidPaths($path, $expectedParameters) {
      $result = FALSE;
      $target = new StartsWith(
        function(HTTP\Request $request, $parameters) use (&$result) {
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
     * @covers \Carica\Io\Network\HTTP\Route\Target\StartsWith
     */
    public function testWithInvalidPaths() {
      $result = FALSE;
      $target = new Match(
        function(HTTP\Request $request, $parameters) use (&$result) {
          return TRUE;
        },
        '/bar/foo'
      );
      $this->assertFalse($target($this->getRequestFixture()));
    }

    private function getRequestFixture() {
      $request = $this
        ->getMockBuilder(HTTP\Request::class)
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
