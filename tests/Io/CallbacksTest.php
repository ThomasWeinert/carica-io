<?php

namespace Carica\Io {

  include_once(__DIR__.'/Bootstrap.php');

  class CallbacksTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testAdd() {
      $callbacks = new Callbacks();
      $callbacks->add('substr');
      $this->assertEquals(
          array('substr'),
          iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testAddWithTwoCalls() {
      $callbacks = new Callbacks();
      $callbacks->add('strpos');
      $callbacks->add('substr');
      $this->assertEquals(
        array('strpos', 'substr'),
        iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testAddWithAnonymusFunction() {
      $callbacks = new Callbacks();
      $callbacks->add($function = function() {});
      $this->assertEquals(
        array($function),
        iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testAddWithCallableObject() {
      $callbacks = new Callbacks();
      $callbacks->add($functor = new Callbacks());
      $this->assertEquals(
        array($functor),
        iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testAddWithCallbackArray() {
      $callbacks = new Callbacks();
      $callbacks->add($callback = array($this, 'testAddWithCallbackArray'));
      $this->assertEquals(
        array($callback),
        iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testClearAfterAddingOneFunction() {
      $callbacks = new Callbacks();
      $callbacks->add('strpos');
      $callbacks->clear();
      $this->assertEquals(
        array(),
        iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testHasExpectingTrue() {
      $callbacks = new Callbacks();
      $callbacks->add('strpos');
      $this->assertTrue(
        $callbacks->has('strpos')
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testHasExpectingFalse() {
      $callbacks = new Callbacks();
      $this->assertFalse(
        $callbacks->has('strpos')
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testFireWithTwoFunctions() {
      $foo = new \stdClass();
      $foo->literal = '';
      $callbacks = new Callbacks();
      $callbacks->add(
        function () use ($foo) {
          $foo->literal .= 'Hello ';
        }
      );
      $callbacks->add(
        function () use ($foo) {
          $foo->literal .= 'World!';
        }
      );
      $callbacks();
      $this->assertEquals('Hello World!', $foo->literal);
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testCountWithThreeFunctions() {
      $callbacks = new Callbacks();
      $callbacks->add(function() {});
      $callbacks->add(function() {});
      $callbacks->add(function() {});
      $this->assertEquals(3, count($callbacks));
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testUseAddMethodAsAnonymusFunction() {
      $callbacks = new Callbacks();
      $add = $callbacks->add;
      $add(function() {});
      $this->assertEquals(1, count($callbacks));
    }
  }
}