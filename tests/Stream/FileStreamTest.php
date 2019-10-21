<?php

namespace Carica\Io\Stream {

  use Carica\Io\Event\Emitter;
  use Carica\Io\Event\Loop;
  use PHPUnit\Framework\MockObject\MockObject;
  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../Bootstrap.php');

  /**
   * @covers \Carica\Io\Stream\FileStream
   */
  class FileStreamTest extends TestCase {

    public function testOpen(): void {
      /** @var MockObject|Loop $loop */
      $loop = $this->createMock(Loop::class);
      $loop
        ->expects($this->once())
        ->method('setStreamReader')
        ->with($this->isType('callable'), $this->isType('resource'));

      $file = new FileStream($loop, __DIR__.'/TestData/sample.txt');
      $this->assertTrue($file->open());
      $this->assertIsResource($file->resource());
    }

    public function testOpenExpectingError(): void {
      /** @var MockObject|Loop $loop */
      $loop = $this->createMock(Loop::class);
      /** @var MockObject|Emitter $events */
      $events = $this
        ->getMockBuilder(Emitter::class)
        ->disableOriginalConstructor()
        ->getMock();
      $events
        ->expects($this->once())
        ->method('emit')
        ->with('error', $this->stringStartsWith('Can not open file: '));

      $file = new FileStream($loop, __DIR__.'/TestData/NON_EXISTING_FILE.txt');
      $file->events($events);
      $this->assertFalse($file->open());
      $this->assertNull($file->resource());
    }

    public function testRead(): void {
      /** @var MockObject|Loop $loop */
      $loop = $this->createMock(Loop::class);
      $loop
        ->expects($this->once())
        ->method('setStreamReader')
        ->with($this->isType('callable'), $this->isType('resource'));

      /** @var MockObject|Emitter $events */
      $events = $this
        ->getMockBuilder(Emitter::class)
        ->disableOriginalConstructor()
        ->getMock();
      $events
        ->expects($this->once())
        ->method('emit')
        ->with('read-data', 'Hello World!');

      $file = new FileStream($loop, __DIR__.'/TestData/sample.txt');
      $file->events($events);
      $file->open();
      $this->assertEquals('Hello World!', $file->read());
    }

    public function testReadWithoutResource(): void {
      /** @var MockObject|Loop $loop */
      $loop = $this->createMock(Loop::class);
      $file = new FileStream($loop, __DIR__.'/TestData/sample.txt');
      $this->assertEquals('', $file->read());
    }

    public function testWriteWithoutResource(): void {
      /** @var MockObject|Loop $loop */
      $loop = $this->createMock(Loop::class);
      $file = new FileStream($loop, __DIR__.'/TestData/sample.txt');
      $this->assertFalse($file->write('foobar'));
    }
  }
}
