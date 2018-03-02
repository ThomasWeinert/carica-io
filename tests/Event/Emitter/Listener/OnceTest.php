<?php

namespace Carica\Io\Event\Emitter\Listener {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../../Bootstrap.php');

  class OnceTest extends TestCase {

    public $calledCallback = FALSE;

    /**
     * @covers \Carica\Io\Event\Emitter\Listener\Once::__invoke
     */
    public function testInvokeCallsCallback() {
      $that = $this;
      $callback = function() use ($that) {
        $that->calledCallback = TRUE;
      };
      $emitter = $this->createMock(\Carica\Io\Event\Emitter::class);
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