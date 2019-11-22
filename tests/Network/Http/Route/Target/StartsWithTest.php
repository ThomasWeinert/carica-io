<?php

namespace Carica\Io\Network\HTTP\Route\Target {

  include_once(__DIR__.'/../../../../Bootstrap.php');

  use Carica\Io\Network\HTTP;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  /**
   * @covers \Carica\Io\Network\HTTP\Route\Target\StartsWith
   */
  class StartsWithTest extends TestCase {

    /**
     * @dataProvider provideValidPaths
     * @param string $path
     * @param array $expectedParameters
     */
    public function testWithValidPaths($path, $expectedParameters): void {
      $result = FALSE;
      $target = new StartsWith(
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
        ['/foo/bar', []],
        ['/foo/{group}', ['group' => 'bar']]
      ];
    }

    public function testWithInvalidPaths(): void {
      $target = new Match(
        function () {
          return $this->createMock(HTTP\Response::class);
        },
        '/bar/foo'
      );
      $this->assertNull($target($this->getRequestFixture()));
    }

    /**
     * @return MockObject|HTTP\Request
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
            ['path', '/foo/bar/file.html']
          ]
        );
      return $request;
    }
  }
}
