<?php

namespace Carica\Io {

  include_once(__DIR__.'/../../../src/Carica/Io/Loader.php');

  class LoaderTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers Carica\Io\Loader
     */
    public function testLoaderLoadsExistingFile() {
      $this->assertTrue(
          Loader::load('Carica\Io\Deferred')
      );
    }

    /**
     * @covers Carica\Io\Loader
     */
    public function testLoaderWithInvalidFile() {
      $this->assertFalse(
        Loader::load('Carica\Io\NonExistingClass')
      );
    }

    /**
     * @covers Carica\Io\Loader
     */
    public function testLoaderWithFileFromUnknownNamespace() {
      $this->assertFalse(
        Loader::load('Carica\StatusMonitor\NonExistingClass')
      );
    }

    /**
     * @covers Carica\Io\Loader
     */
    public function testMapSortsByLength() {
      Loader::map(
        array(
          'Sample' => 'fail',
          'Sample\Foo' => 'success'
        )
      );
      $this->assertEquals(
        str_replace('/', DIRECTORY_SEPARATOR, 'success/File.php'),
        Loader::getFileName('Sample\\Foo\\File')
      );
    }
  }
}