<?php

namespace Carica\Io\Network\Http\Route\Target {

  include_once(__DIR__.'/../../../../Bootstrap.php');

  use Carica\Io\Network\Http;

  class AnyTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Network\Http\Route\Target\Any
     */
    public function testWithoutLimitation() {
      $result = FALSE;
      $target = new Any(
        function() use (&$result) { $result = TRUE; }
      );
      $target($this->getRequestFixture());
      $this->assertTrue($result);
    }

    /**
     * @covers Carica\Io\Network\Http\Route\Target\Any
     */
    public function testMethodsIncludeCurrent() {
      $result = FALSE;
      $target = new Any(
        function() use (&$result) { $result = TRUE; }
      );
      $target->methods('GET POST');
      $target($this->getRequestFixture('POST'));
      $this->assertTrue($result);
    }

    /**
     * @covers Carica\Io\Network\Http\Route\Target\Any
     */
    public function testMethodsNotIncludeCurrent() {
      $result = FALSE;
      $target = new Any(
        function() use (&$result) { $result = TRUE; }
      );
      $target->methods('POST');
      $target($this->getRequestFixture('GET'));
      $this->assertFalse($result);
    }

    /**
     * @covers Carica\Io\Network\Http\Route\Target\Any
     */
    public function testMethodsWithInvalidValue() {
      $result = FALSE;
      $target = new Any(
        function() use (&$result) { $result = TRUE; }
      );
      $this->setExpectedException('InvalidArgumentException');
      $target->methods('123');
    }

    private function getRequestFixture($method = 'GET') {
      $request = $this
        ->getMockBuilder('Carica\Io\Network\Http\Request')
        ->disableOriginalConstructor()
        ->getMock();
      $request
        ->expects($this->any())
        ->method('__get')
        ->with('method')
        ->will($this->returnValue($method));
      return $request;
    }
  }
}