<?php

namespace Carica\Io\Event\Emitter\Listener {

  use Carica\Io\Event\Emitter as EventEmitter;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../../Bootstrap.php');

  /**
   * @covers \Carica\Io\Event\Emitter\Listener\Once
   */
  class OnceTest extends TestCase {

    public $calledCallback = FALSE;

    public function testInvokeCallsCallback(): void {
      $callback = function() {
        $this->calledCallback = TRUE;
      };
      /** @var EventEmitter|MockObject $emitter */
      $emitter = $this->createMock(EventEmitter::class);
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
