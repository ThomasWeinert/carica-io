<?php

namespace Carica\Io\Event\Emitter\Listener {

  include_once(__DIR__.'/../../../Bootstrap.php');

  class OnTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Event\Emitter\Listener\On::__construct
     */
    public function testConstructor() {
      $emitter = $this->getMock('Carica\Io\Event\Emitter');
      $callback = function() {};
      $event = new On($emitter, 'foo', $callback);
      $this->assertSame($emitter, $event->emitter);
      $this->assertEquals('foo', $event->event);
      $this->assertSame($callback, $event->callback);
    }
  }
}