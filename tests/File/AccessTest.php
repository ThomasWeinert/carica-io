<?php

namespace Carica\Io\File {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/../Bootstrap.php');

  class AccessTest extends TestCase {

    /**
     * @covers \Carica\Io\File\Access::getInfo
     */
    public function testGetInfo(): void {
      $fileSystem = new Access();
      $info = $fileSystem->getInfo(__FILE__);
      $this->assertNotNull($info);
      $this->assertEquals(
        __FILE__,
        (string)$info
      );
    }

    /**
     * @covers \Carica\Io\File\Access::getFile
     */
    public function testGetFile(): void {
      $fileSystem = new Access();
      $file = $fileSystem->getFile(__FILE__);
      $this->assertInstanceOf('splFileObject', $file);
    }

    /**
     * @covers \Carica\Io\File\Access::getFile
     */
    public function testGetFileWithContext(): void {
      $fileSystem = new Access();
      $file = $fileSystem->getFile(__FILE__, 'r', stream_context_create(array()));
      $this->assertInstanceOf('splFileObject', $file);
    }

    /**
     * @covers \Carica\Io\File\Access::getFileResource
     */
    public function testGetFileResource(): void {
      $fileSystem = new Access();
      $this->assertIsResource($fh = $fileSystem->getFileResource(__FILE__));
      fclose($fh);
    }

    /**
     * @covers \Carica\Io\File\Access::getFileResource
     */
    public function testGetFileResourceWithContext(): void {
      $fileSystem = new Access();
      $this->assertIsResource(
        $fh = $fileSystem->getFileResource(__FILE__, 'r', stream_context_create(array()))
      );
      fclose($fh);
    }

    /**
     * @covers \Carica\Io\File\Access::getMimeType
     */
    public function testGetMimeTypeExpectingPhp(): void {
      if (!function_exists('mime_content_type')) {
        $this->markTestSkipped('Function "mime_content_type()" not available.');
      }
      $fileSystem = new Access();
      $this->assertEquals(
        'text/x-php', $fileSystem->getMimeType(__FILE__)
      );
    }

    /**
     * @covers \Carica\Io\File\Access::getMimeType
     */
    public function testGetMimeTypeUsingExtensionMapping(): void {
      $fileSystem = new Access();
      $this->assertEquals(
        'text/css', $fileSystem->getMimeType('sample.css')
      );
    }

    /**
     * @covers \Carica\Io\File\Access::getRealPath
     */
    public function testGetRealPath(): void {
      $fileSystem = new Access();
      $this->assertEquals(
        __FILE__,
        $fileSystem->getRealPath(__DIR__.'/././AccessTest.php')
      );
    }
  }
}
