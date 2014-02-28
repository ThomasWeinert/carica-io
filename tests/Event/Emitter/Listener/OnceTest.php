<?php

namespace Carica\Io\Event\Emitter\Listener {

  include_once(__DIR__.'/../../../Bootstrap.php');

  class OnceTest extends \PHPUnit_Framework_TestCase {

    public $calledCallback = FALSE;

    /**
     * @covers Carica\Io\Event\Emitter\Listener\Once::__invoke
     */
    public function testInvokeCallsCallback() {
      $that = $this;
      $callback = function() use ($that) {
        $that->calledCallback = TRUE;
      };
      $emitter = $this->getMock('Carica\\Io\\Event\\Emitter');
      $emitter
        ->expects($this->once())
        ->method('removeListener')
        ->with('foo', $callback);
      $event = new Once(
        $emitter,
        'foo',
        $callback
      );
      $event();
      $this->assertTrue($this->calledCallback);
    }
  }
}