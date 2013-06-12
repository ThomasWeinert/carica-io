<?php

namespace Carica\Io {

  include_once(__DIR__.'/Bootstrap.php');

  class FileSystemTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\FileSystem::getInfo
     */
    public function testGetInfo() {
      $fileSystem = new FileSystem();
      $info = $fileSystem->getInfo(__FILE__);
      $this->assertInstanceOf('splFileInfo', $info);
      $this->assertEquals(
        __FILE__,
        (string)$info
      );
    }

    /**
     * @covers Carica\Io\FileSystem::getFile
     */
    public function testGetFile() {
      $fileSystem = new FileSystem();
      $file = $fileSystem->getFile(__FILE__);
      $this->assertInstanceOf('splFileObject', $file);
    }

    /**
     * @covers Carica\Io\FileSystem::getFile
     */
    public function testGetFileWithContext() {
      $fileSystem = new FileSystem();
      $file = $fileSystem->getFile(__FILE__, 'r', stream_context_create(array()));
      $this->assertInstanceOf('splFileObject', $file);
    }

    /**
     * @covers Carica\Io\FileSystem::getFileResource
     */
    public function testGetFileResource() {
      $fileSystem = new FileSystem();
      $this->assertInternalType('resource', $fh = $fileSystem->getFileResource(__FILE__));
      fclose($fh);
    }

    /**
     * @covers Carica\Io\FileSystem::getFileResource
     */
    public function testGetFileResourceWithContext() {
      $fileSystem = new FileSystem();
      $this->assertInternalType(
        'resource',
        $fh = $fileSystem->getFileResource(__FILE__, 'r', stream_context_create(array()))
      );
      fclose($fh);
    }

    /**
     * @covers Carica\Io\FileSystem::getMimeType
     */
    public function testGetMimeTypeExpectingPhp() {
      if (!function_exists('mime_content_type')) {
        $this->markTestSkipped('Function "mime_content_type()" not available.');
      }
      $fileSystem = new FileSystem();
      $this->assertEquals(
        'text/x-php', $fileSystem->getMimeType(__FILE__)
      );
    }

    /**
     * @covers Carica\Io\FileSystem::getMimeType
     */
    public function testGetMimeTypeUsingExtensionMapping() {
      $fileSystem = new FileSystem();
      $this->assertEquals(
        'text/css', $fileSystem->getMimeType('sample.css')
      );
    }

    /**
     * @covers Carica\Io\FileSystem::getRealPath
     */
    public function testGetRealPath() {
      $fileSystem = new FileSystem();
      $this->assertEquals(
        __FILE__,
        $fileSystem->getRealPath(__DIR__.'/././FileSystemTest.php')
      );
    }
  }
}