<?php

namespace Carica\Io\Event {

  include_once(__DIR__.'/../Bootstrap.php');

  class DeferredTest extends \PHPUnit_Framework_TestCase {

    public function testResolve() {
      $literal = '';
      $defer = new Deferred();
      $defer->done(
        function($text) use (&$literal) {
          $literal = $text;
        }
      );
      $defer->resolve('success');
      $this->assertEquals('success', $literal);
    }

    public function testReject() {
      $literal = '';
      $defer = new Deferred();
      $defer->failed(
        function($text) use (&$literal) {
          $literal = $text;
        }
      );
      $defer->reject('got error');
      $this->assertEquals('got error', $literal);
    }
  }
}