<?php

namespace Carica\Io\Network\HTTP\Route {

  include_once(__DIR__.'/../../../Bootstrap.php');

  use Carica\Io\Network\HTTP;
  use PHPUnit\Framework\TestCase;

  class TargetTest extends TestCase {

    /**
     * @covers \Carica\Io\Network\HTTP\Route\Target
     */
    public function testConstructor(): void {
      $target = new Target_TestProxy($callback = static function() {});
      $this->assertSame($callback, $target->getCallback());
    }

    /**
     * @covers \Carica\Io\Network\HTTP\Route\Target
     */
    public function testCallableInterfaceValidationSuccessful(): void {
      $request = $this
        ->getMockBuilder(HTTP\Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $target = new Target_TestProxy(
        static function() {
          return TRUE;
        }
      );
      $target->validationResult = array();
      $this->assertTrue($target($request));
    }
    /**
     * @covers \Carica\Io\Network\HTTP\Route\Target
     */
    public function testCallableInterfaceVaildationFailed(): void {
      $request = $this
        ->getMockBuilder(HTTP\Request::class)
        ->disableOriginalConstructor()
        ->getMock();
      $target = new Target_TestProxy(
        static function() {
          return TRUE;
        }
      );
      $target->validationResult = FALSE;
      $this->assertFalse($target($request));
    }
  }

  class Target_TestProxy extends Target {

    public $validationResult;

    public function validate(HTTP\Request $request) {
      return $this->validationResult;
    }
  }
}
