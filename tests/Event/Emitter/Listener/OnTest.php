<?php

namespace Carica\Io\Event\Emitter\Listener {

  use Carica\Io\Event\Emitter;
  use LogicException;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../../../Bootstrap.php');

  /**
   * @covers \Carica\Io\Event\Emitter\Listener\On
   */
  class OnTest extends TestCase {

    public $calledCallback = FALSE;

    public function testConstructor(): void {
      $emitter = $this->createEmitterFixture();
      $callback = static function () {
      };
      $event = new On($emitter, 'foo', $callback);
      $this->assertSame($emitter, $event->emitter);
      $this->assertEquals('foo', $event->event);
      $this->assertSame($callback, $event->callback);
    }

    /**
     * @param $property
     * @dataProvider provideValidProperties
     */
    public function testPropertyIsset($property): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        static function () {
        }
      );
      $this->assertTrue(isset($event->$property));
    }

    public function testPropertyIssetWithInvalidPropertyExpectingFalse(): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        static function () {
        }
      );
      $this->assertFalse(isset($event->INVALID_PROPERTY));
    }

    public function testGetPropertyEmitter(): void {
      $event = new On(
        $emitter = $this->createEmitterFixture(),
        'foo',
        static function () {
        }
      );
      $this->assertSame($emitter, $event->emitter);
    }

    public function testGetPropertyEvent(): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        static function () {
        }
      );
      $this->assertEquals('foo', $event->event);
    }

    public function testGetPropertyCallback(): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        $callback = static function () {
        }
      );
      $this->assertSame($callback, $event->callback);
    }

    public function testGetCallback(): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        $callback = static function () {
        }
      );
      $this->assertSame($callback, $event->getCallback());
    }

    public function testGetInvalidPropertyExpectingException(): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        static function () {
        }
      );
      $this->expectException(LogicException::class);
      /** @noinspection PhpUndefinedFieldInspection */
      $event->INVALID_PROPERTY;
    }

    public function testSetIsBlockedExpectingException(): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        static function () {
        }
      );
      $this->expectException(LogicException::class);
      $event->emitter = $this->createEmitterFixture();
    }

    public function testUnsetIsBlockedExpectingException(): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        static function () {
        }
      );
      $this->expectException(LogicException::class);
      unset($event->emitter);
    }

    public function testInvokeCallsCallback(): void {
      $event = new On(
        $this->createEmitterFixture(),
        'foo',
        function () {
          $this->calledCallback = TRUE;
        }
      );
      $event();
      $this->assertTrue($this->calledCallback);
    }

    /**************************
     * Data Provider
     *************************/

    public static function provideValidProperties(): array {
      return [
        ['emitter'],
        ['event'],
        ['callback']
      ];
    }

    /**
     * @return MockObject|Emitter
     */
    private function createEmitterFixture() {
      return $this->createMock(Emitter::class);
    }
  }
}
