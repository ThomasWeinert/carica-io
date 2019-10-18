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
        function () use (&$result) {
          $result = TRUE;
        },
        '/foo'
      );
      $target->methods('POST');
      $target($this->getRequestFixture('GET'));
      $this->assertFalse($result);
    }

    /**
     * @covers       \Carica\Io\Network\Http\Route\Target\Match
     * @dataProvider provideValidPaths
     */
    public function testWithValidPaths($path, $expectedParameters) {
      $result = FALSE;
      $target = new Match(
        function (Http\Request $request, $parameters) use (&$result) {
          $result = $parameters;
          return TRUE;
        },
        $path
      );
      $this->assertTrue($target($this->getRequestFixture()));
      $this->assertEquals($expectedParameters, $result);
    }

    public static function provideValidPaths() {
      return [
        ['/foo/bar/42', []],
        ['/foo/bar/{id}', ['id' => 42]],
        ['/foo/{group}/{id}', ['group' => 'bar', 'id' => 42]],
        ['/foo/{group}/42', ['group' => 'bar']]
      ];
    }

    /**
     * @covers       \Carica\Io\Network\Http\Route\Target\Match
     * @dataProvider provideInvalidPaths
     */
    public function testWithInvalidPaths($path) {
      $result = FALSE;
      $target = new Match(
        function (Http\Request $request, $parameters) use (&$result) {
          return TRUE;
        },
        $path
      );
      $this->assertFalse($target($this->getRequestFixture()));
    }

    public static function provideInvalidPaths() {
      return [
        'to short' => ['/foo'],
        'to long' => ['/foo/bar/42/detail'],
        'different element' => ['/foo/bar/23']
      ];
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
            [
              ['method', 'GET'],
              ['path', '/foo/bar/42']
            ]
          )
        );
      return $request;
    }
  }
}
