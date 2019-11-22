<?php

namespace Carica\Io\Network\HTTP\Route\Target {

  include_once(__DIR__.'/../../../../Bootstrap.php');

  use Carica\Io\Network\HTTP;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  /**
     * @covers \Carica\Io\Network\HTTP\Route\Target\Match
   */
  class MatchTest extends TestCase {

    public function testWithInvalidMethod(): void {
      $result = FALSE;
      $target = new Match(
        static function () use (&$result) {
          $result = TRUE;
        },
        '/foo'
      );
      $target->methods('POST');
      $target($this->getRequestFixture());
      $this->assertFalse($result);
    }

    /**
     * @dataProvider provideValidPaths
     * @param $path
     * @param $expectedParameters
     */
    public function testWithValidPaths($path, $expectedParameters): void {
      $result = FALSE;
      $target = new Match(
        function (/** @noinspection PhpUnusedParameterInspection */ HTTP\Request $request, $parameters) use (&$result) {
          $result = $parameters;
          return $this->createMock(HTTP\Response::class);
        },
        $path
      );
      $this->assertNotNull($target($this->getRequestFixture()));
      $this->assertEquals($expectedParameters, $result);
    }

    public static function provideValidPaths(): array {
      return [
        ['/foo/bar/42', []],
        ['/foo/bar/{id}', ['id' => 42]],
        ['/foo/{group}/{id}', ['group' => 'bar', 'id' => 42]],
        ['/foo/{group}/42', ['group' => 'bar']]
      ];
    }

    /**
     * @dataProvider provideInvalidPaths
     * @param $path
     */
    public function testWithInvalidPaths($path): void {
      $target = new Match(
        static function () {
          return $this->createMock(HTTP\Response::class);
        },
        $path
      );
      $this->assertNull($target($this->getRequestFixture()));
    }

    public static function provideInvalidPaths(): array {
      return [
        'to short' => ['/foo'],
        'to long' => ['/foo/bar/42/detail'],
        'different element' => ['/foo/bar/23']
      ];
    }

    /**
     * @return HTTP\Request|MockObject
     */
    private function getRequestFixture() {
      $request = $this
        ->getMockBuilder(HTTP\Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $request
        ->method('__get')
        ->willReturnMap(
          [
            ['method', 'GET'],
            ['path', '/foo/bar/42']
          ]
        );
      return $request;
    }
  }
}
