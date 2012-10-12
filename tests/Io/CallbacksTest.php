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
      $callbacks
        ->add('strpos')
        ->add('substr');
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
    public function testRemove() {
      $callbacks = new Callbacks();
      $callbacks
        ->add('strpos')
        ->remove('strpos')
        ->add('substr');
      $this->assertEquals(
        array('substr'),
        iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testClearAfterAddingOneFunction() {
      $callbacks = new Callbacks();
      $callbacks
        ->add('strpos')
        ->clear()
        ->add('substr');
      $this->assertEquals(
        array('substr'),
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
    public function testLockBlocksAdd() {
      $callbacks = new Callbacks();
      $callbacks
        ->lock()
        ->add('substr');
      $this->assertEquals(
          array(),
          iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testLockBlocksRemove() {
      $callbacks = new Callbacks();
      $callbacks
        ->add('substr')
        ->lock()
        ->remove('substr');
      $this->assertEquals(
          array('substr'),
          iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testLockBlocksClear() {
      $callbacks = new Callbacks();
      $callbacks
        ->add('substr')
        ->lock()
        ->clear();
      $this->assertEquals(
        array('substr'),
        iterator_to_array($callbacks)
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testLockedExpectingTrue() {
      $callbacks = new Callbacks();
      $callbacks->lock();
      $this->assertTrue(
        $callbacks->locked()
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testLockedExpectingFalse() {
      $callbacks = new Callbacks();
      $this->assertFalse(
        $callbacks->locked()
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testFireWithTwoFunctions() {
      $foo = new \stdClass();
      $foo->literal = '';
      $callbacks = new Callbacks();
      $callbacks
        ->add(
          function () use ($foo) {
            $foo->literal .= 'Hello ';
          }
        )
        ->add(
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
    public function testFiredExpectingTrue() {
      $callbacks = new Callbacks();
      $callbacks();
      $this->assertTrue(
        $callbacks->fired()
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testFiredExpectingFalse() {
      $callbacks = new Callbacks();
      $this->assertFalse(
        $callbacks->fired()
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testDisableBlocksExecution() {
      $foo = new \stdClass();
      $foo->literal = '';
      $callbacks = new Callbacks();
      $callbacks->add(
        function () use ($foo) {
          $foo->literal .= 'Hello World!';
        }
      );
      $callbacks->disable();
      $callbacks();
      $this->assertEquals('', $foo->literal);
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testDisabledExpectingTrue() {
      $callbacks = new Callbacks();
      $callbacks->disable();
      $this->assertTrue(
          $callbacks->disabled()
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testDisabledExpectingFalse() {
      $callbacks = new Callbacks();
      $this->assertFalse(
          $callbacks->disabled()
      );
    }

    /**
     * @covers Carica\Io\Callbacks
     */
    public function testCountWithThreeFunctions() {
      $callbacks = new Callbacks();
      $callbacks
        ->add(function() {})
        ->add(function() {})
        ->add(function() {});
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