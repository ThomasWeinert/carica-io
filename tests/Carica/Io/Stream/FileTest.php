<?php

namespace Carica\Io\Stream {

  include_once(__DIR__.'/../Bootstrap.php');

  class FileTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Stream\File
     */
    public function testConstructor() {
      $file = new File('sample.txt');
      $this->assertAttributeEquals(
        'sample.txt', '_filename', $file
      );
      $this->assertAttributeEquals(
        'r', '_mode', $file
      );
    }

    /**
     * @covers Carica\Io\Stream\File
     */
    public function testOpen() {
      $loop = $this->getMock('Carica\Io\Event\Loop');
      $loop
        ->expects($this->once())
        ->method('setStreamReader')
        ->with($this->isType('callable'), $this->isType('resource'));

      $file = new File(__DIR__.'/TestData/sample.txt');
      $file->loop($loop);
      $this->assertTrue($file->open());
      $this->assertInternalType('resource', $file->resource());
    }

    /**
     * @covers Carica\Io\Stream\File
     */
    public function testOpenExpectingError() {
      $events = $this
        ->getMockBuilder('Carica\Io\Event\Emitter')
        ->disableOriginalConstructor()
        ->getMock();
      $events
        ->expects($this->once())
        ->method('emit')
        ->with('error', $this->stringStartsWith('Can not open file: '));

      $file = new File(__DIR__.'/TestData/NON_EXISTING_FILE.txt');
      $file->events($events);
      $this->assertFalse($file->open());
      $this->assertNull($file->resource());
    }

    /**
     * @covers Carica\Io\Stream\File
     */
    public function testRead() {
      $loop = $this->getMock('Carica\Io\Event\Loop');
      $loop
        ->expects($this->once())
        ->method('setStreamReader')
        ->with($this->isType('callable'), $this->isType('resource'));

      $events = $this
        ->getMockBuilder('Carica\Io\Event\Emitter')
        ->disableOriginalConstructor()
        ->getMock();
      $events
        ->expects($this->once())
        ->method('emit')
        ->with('read-data', 'Hello World!');

      $file = new File(__DIR__.'/TestData/sample.txt');
      $file->loop($loop);
      $file->events($events);
      $file->open();
      $this->assertEquals('Hello World!', $file->read());
    }

    /**
     * @covers Carica\Io\Stream\File
     */
    public function testReadWithoutResource() {
      $file = new File(__DIR__.'/TestData/sample.txt');
      $this->assertEquals('', $file->read());
    }

    /**
     * @covers Carica\Io\Stream\File
     */
    public function testWriteWithoutResource() {
      $file = new File(__DIR__.'/TestData/sample.txt');
      $this->assertFalse($file->write('foobar'));
    }
  }
}