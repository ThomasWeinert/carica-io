<?php

namespace Carica\Io {

  include_once(__DIR__.'/Bootstrap.php');

  class DeferredTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Event\Deferred
     */
    public function testResolve() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->done(
          function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->resolve('success');
      $this->assertEquals('success', $literal);
    }

    /**
     * @covers Carica\Io\Event\Deferred
     */
    public function testReject() {
      $literal = '';
      $defer = new Deferred();
      $defer
        ->fail(
          function($text) use (&$literal) {
            $literal = $text;
          }
        )
        ->reject('got error');
      $this->assertEquals('got error', $literal);
    }

    /**
     * @covers Carica\Io\Event\Deferred
     */
    public function testPromise() {
      $defer = new Deferred();
      $promise = $defer->promise();
      $this->assertInstanceOf('Carica\Io\Deferred\Promise', $promise);
      $this->assertAttributeSame(
        $defer, '_defer', $promise
      );
    }
  }
}