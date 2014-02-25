<?php

namespace Carica\Io\File {

  include_once(__DIR__.'/../Bootstrap.php');

  class FileAccessTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\File\Access::getInfo
     */
    public function testGetInfo() {
      $fileSystem = new Access();
      $info = $fileSystem->getInfo(__FILE__);
      $this->assertInstanceOf('splFileInfo', $info);
      $this->assertEquals(
        __FILE__,
        (string)$info
      );
    }

    /**
     * @covers Carica\Io\File\Access::getFile
     */
    public function testGetFile() {
      $fileSystem = new Access();
      $file = $fileSystem->getFile(__FILE__);
      $this->assertInstanceOf('splFileObject', $file);
    }

    /**
     * @covers Carica\Io\File\Access::getFile
     */
    public function testGetFileWithContext() {
      $fileSystem = new Access();
      $file = $fileSystem->getFile(__FILE__, 'r', stream_context_create(array()));
      $this->assertInstanceOf('splFileObject', $file);
    }

    /**
     * @covers Carica\Io\File\Access::getFileResource
     */
    public function testGetFileResource() {
      $fileSystem = new Access();
      $this->assertInternalType('resource', $fh = $fileSystem->getFileResource(__FILE__));
      fclose($fh);
    }

    /**
     * @covers Carica\Io\File\Access::getFileResource
     */
    public function testGetFileResourceWithContext() {
      $fileSystem = new Access();
      $this->assertInternalType(
        'resource',
        $fh = $fileSystem->getFileResource(__FILE__, 'r', stream_context_create(array()))
      );
      fclose($fh);
    }

    /**
     * @covers Carica\Io\File\Access::getMimeType
     */
    public function testGetMimeTypeExpectingPhp() {
      if (!function_exists('mime_content_type')) {
        $this->markTestSkipped('Function "mime_content_type()" not available.');
      }
      $fileSystem = new Access();
      $this->assertEquals(
        'text/x-php', $fileSystem->getMimeType(__FILE__)
      );
    }

    /**
     * @covers Carica\Io\File\Access::getMimeType
     */
    public function testGetMimeTypeUsingExtensionMapping() {
      $fileSystem = new Access();
      $this->assertEquals(
        'text/css', $fileSystem->getMimeType('sample.css')
      );
    }

    /**
     * @covers Carica\Io\File\Access::getRealPath
     */
    public function testGetRealPath() {
      $fileSystem = new Access();
      $this->assertEquals(
        __FILE__,
        $fileSystem->getRealPath(__DIR__.'/././AccessTest.php')
      );
    }
  }
}