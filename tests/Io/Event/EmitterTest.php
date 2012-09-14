<?php

namespace Carica\Io\Event {

  include_once(__DIR__.'/../Bootstrap.php');

  class EmitterTest extends \PHPUnit_Framework_TestCase {

    public function testOnAddListener() {
      $event = $this
        ->getMockBuilder('Carica\Io\Event\Emitter\Listener\On')
        ->disableOriginalConstructor()
        ->getMock();
      $emitter = new Emitter();
      $emitter->on('foo', $event);
      $this->assertEquals(
        array($event), $emitter->listeners('foo')
      );
    }

  }
}
