<?php

namespace Carica\Io\Network\HTTP\Route {

  include_once(__DIR__.'/../../../Bootstrap.php');

  use Carica\Io\Network\HTTP;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  /**
   * @covers \Carica\Io\Network\HTTP\Route\Target
   */
  class TargetTest extends TestCase {

    public function testConstructor(): void {
      $target = new Target_TestProxy($callback = static function() {});
      $this->assertSame($callback, $target->getCallback());
    }

    public function testCallableInterfaceValidationSuccessful(): void {
      /** @var MockObject|HTTP\Request $request */
      $request = $this
        ->getMockBuilder(HTTP\Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $target = new Target_TestProxy(
        function() {
          return $this->createMock(HTTP\Response::class);
        }
      );
      $target->validationResult = array();
      $this->assertNotNull($target($request));
    }

    public function testCallableInterfaceValidationFailed(): void {
      /** @var MockObject|HTTP\Request $request */
      $request = $this
        ->getMockBuilder(HTTP\Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $target = new Target_TestProxy(
        function() {
          return $this->createMock(HTTP\Response::class);
        }
      );
      $target->validationResult = NULL;
      $this->assertNull($target($request));
    }
  }

  class Target_TestProxy extends Target {

    public $validationResult;

    public function prepare(HTTP\Request $request): ?array {
      return $this->validationResult;
    }
  }
}
