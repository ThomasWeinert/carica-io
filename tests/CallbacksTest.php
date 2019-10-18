<?php
declare(strict_types=1);

namespace Carica\Io {

  use PHPUnit\Framework\TestCase;
  use stdClass;

  include_once(__DIR__.'/Bootstrap.php');

  /**
   * @covers \Carica\Io\Callbacks
   */
  class CallbacksTest extends TestCase {

    public function testAdd(): void {
      $callbacks = new Callbacks();
      $callbacks->add('substr');
      $this->assertEquals(
        ['substr'],
        iterator_to_array($callbacks)
      );
    }

    public function testAddWithTwoCalls(): void {
      $callbacks = new Callbacks();
      $callbacks
        ->add('strpos')
        ->add('substr');
      $this->assertEquals(
        ['strpos', 'substr'],
        iterator_to_array($callbacks)
      );
    }

    public function testAddWithAnonymousFunction(): void {
      $callbacks = new Callbacks();
      $callbacks->add(
        $function = static function () {
        }
      );
      $this->assertEquals(
        [$function],
        iterator_to_array($callbacks)
      );
    }

    public function testAddWithCallableObject(): void {
      $callbacks = new Callbacks();
      $callbacks->add($functor = new Callbacks());
      $this->assertEquals(
        [$functor],
        iterator_to_array($callbacks)
      );
    }

    public function testAddWithCallbackArray(): void {
      $callbacks = new Callbacks();
      $callbacks->add($callback = [$this, 'testAddWithCallbackArray']);
      $this->assertEquals(
        [$callback],
        iterator_to_array($callbacks)
      );
    }

    public function testRemove(): void {
      $callbacks = new Callbacks();
      $callbacks
        ->add('strpos')
        ->remove('strpos')
        ->add('substr');
      $this->assertEquals(
        ['substr'],
        iterator_to_array($callbacks)
      );
    }

    public function testClearAfterAddingOneFunction(): void {
      $callbacks = new Callbacks();
      $callbacks
        ->add('strpos')
        ->clear()
        ->add('substr');
      $this->assertEquals(
        ['substr'],
        iterator_to_array($callbacks)
      );
    }

    public function testHasExpectingTrue(): void {
      $callbacks = new Callbacks();
      $callbacks->add('strpos');
      $this->assertTrue(
        $callbacks->has('strpos')
      );
    }

    public function testHasExpectingFalse(): void {
      $callbacks = new Callbacks();
      $this->assertFalse(
        $callbacks->has('strpos')
      );
    }

    public function testLockBlocksAdd(): void {
      $callbacks = new Callbacks();
      $callbacks
        ->lock()
        ->add('substr');
      $this->assertEquals(
        [],
        iterator_to_array($callbacks)
      );
    }

    public function testLockBlocksRemove(): void {
      $callbacks = new Callbacks();
      $callbacks
        ->add('substr')
        ->lock()
        ->remove('substr');
      $this->assertEquals(
        ['substr'],
        iterator_to_array($callbacks)
      );
    }

    public function testLockBlocksClear(): void {
      $callbacks = new Callbacks();
      $callbacks
        ->add('substr')
        ->lock()
        ->clear();
      $this->assertEquals(
        ['substr'],
        iterator_to_array($callbacks)
      );
    }

    public function testLockedExpectingTrue(): void {
      $callbacks = new Callbacks();
      $callbacks->lock();
      $this->assertTrue(
        $callbacks->locked()
      );
    }

    public function testLockedExpectingFalse(): void {
      $callbacks = new Callbacks();
      $this->assertFalse(
        $callbacks->locked()
      );
    }

    public function testFireWithTwoFunctions(): void {
      $foo = new stdClass();
      $foo->literal = '';
      $callbacks = new Callbacks();
      $callbacks
        ->add(
          static function () use ($foo) {
            $foo->literal .= 'Hello ';
          }
        )
        ->add(
          static function () use ($foo) {
            $foo->literal .= 'World!';
          }
        );
      $callbacks();
      $this->assertEquals('Hello World!', $foo->literal);
    }

    public function testFiredExpectingTrue(): void {
      $callbacks = new Callbacks();
      $callbacks();
      $this->assertTrue(
        $callbacks->fired()
      );
    }

    public function testFiredExpectingFalse(): void {
      $callbacks = new Callbacks();
      $this->assertFalse(
        $callbacks->fired()
      );
    }

    public function testDisableBlocksExecution(): void {
      $foo = new stdClass();
      $foo->literal = '';
      $callbacks = new Callbacks();
      $callbacks->add(
        static function () use ($foo) {
          $foo->literal .= 'Hello World!';
        }
      );
      $callbacks->disable();
      $callbacks();
      $this->assertEquals('', $foo->literal);
    }

    public function testDisabledExpectingTrue(): void {
      $callbacks = new Callbacks();
      $callbacks->disable();
      $this->assertTrue(
        $callbacks->disabled()
      );
    }

    public function testDisabledExpectingFalse(): void {
      $callbacks = new Callbacks();
      $this->assertFalse(
        $callbacks->disabled()
      );
    }

    public function testCountWithThreeFunctions(): void {
      $callbacks = new Callbacks();
      $callbacks
        ->add(
          static function () {
          }
        )
        ->add(
          static function () {
          }
        )
        ->add(
          static function () {
          }
        );
      $this->assertCount(3, $callbacks);
    }

    public function testUseAddMethodAsAnonymousFunction(): void {
      $callbacks = new Callbacks();
      $add = $callbacks->add;
      $add(
        static function () {
        }
      );
      $this->assertCount(1, $callbacks);
    }

    public function testMagicGetWithInvalidPropertyExpectingException(): void {
      $callbacks = new Callbacks();
      $this->expectException(\LogicException::class);
      $callbacks->invalidProperty;
    }

    public function testMagicSetExpectingException(): void {
      $callbacks = new Callbacks();
      $this->expectException(\LogicException::class);
      $callbacks->add = 'fail';
    }
  }
}
