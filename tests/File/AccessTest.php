<?php

namespace Carica\Io\File {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../Bootstrap.php');

  /**
   * @covers \Carica\Io\File\Access
   */
  class AccessTest extends TestCase {

    public function testGetInfo(): void {
      $fileSystem = new Access();
      $info = $fileSystem->getInfo(__FILE__);
      $this->assertNotNull($info);
      $this->assertEquals(
        __FILE__,
        (string)$info
      );
    }

    public function testGetFile(): void {
      $fileSystem = new Access();
      $file = $fileSystem->getFile(__FILE__);
      $this->assertInstanceOf('splFileObject', $file);
    }

    public function testGetFileWithContext(): void {
      $fileSystem = new Access();
      $file = $fileSystem->getFile(__FILE__, 'r', stream_context_create(array()));
      $this->assertInstanceOf('splFileObject', $file);
    }

    public function testGetFileResource(): void {
      $fileSystem = new Access();
      $this->assertIsResource($fh = $fileSystem->getFileResource(__FILE__));
      fclose($fh);
    }

    public function testGetFileResourceWithContext(): void {
      $fileSystem = new Access();
      $this->assertIsResource(
        $fh = $fileSystem->getFileResource(__FILE__, 'r', stream_context_create(array()))
      );
      fclose($fh);
    }

    public function testGetMimeTypeExpectingPhp(): void {
      if (!function_exists('mime_content_type')) {
        $this->markTestSkipped('Function "mime_content_type()" not available.');
      }
      $fileSystem = new Access();
      $this->assertEquals(
        'text/x-php', $fileSystem->getMimeType(__FILE__)
      );
    }

    public function testGetMimeTypeUsingExtensionMapping(): void {
      $fileSystem = new Access();
      $this->assertEquals(
        'text/css', $fileSystem->getMimeType('sample.css')
      );
    }

    public function testGetRealPath(): void {
      $fileSystem = new Access();
      $this->assertEquals(
        __FILE__,
        $fileSystem->getRealPath(__DIR__.'/././AccessTest.php')
      );
    }
  }
}
