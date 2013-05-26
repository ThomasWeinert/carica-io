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
    public function testMapMatchesMostExact() {
      Loader::map(
        array(
          'Sample\Foo\Bar' => 'fail',
          'Sample' => 'fail',
          'FooBar' => 'fail',
          'Sample\Foo' => 'success'
        )
      );
      $this->assertEquals(
        str_replace('/', DIRECTORY_SEPARATOR, 'success/File.php'),
        Loader::getFileName('Sample\\Foo\\File')
      );
    }

    /**
     * @covers Carica\Io\Loader
     */
    public function testMapSortsByLength() {
      Loader::map(
        array(
          'Sample\Foo\Bar' => 'fail',
          'Sample' => 'fail',
          'FooBar' => 'fail',
          'Sample\Foo' => 'success'
        )
      );
      $this->assertEquals(
        array(
          'Sample\Foo\Bar\\' => 'fail',
          'Sample\Foo\\' => 'success',
          'Sample\\' => 'fail',
          'FooBar\\' => 'fail'
        ),
        Loader::map()
      );
    }

    /**
     * @covers Carica\Io\Loader
     */
    public function testMapNullValueRemovesMapping() {
      Loader::map(
        array(
          'Sample\Foo\Bar' => 'success',
          'Sample' => 'fail',
          'FooBar' => 'success',
          'Sample\Foo' => 'fail'
        )
      );
      Loader::map(
        array(
          'Sample' => NULL,
          'Sample\Foo' => NULL
        )
      );
      $this->assertEquals(
        array(
          'Sample\Foo\Bar\\' => 'success',
          'FooBar\\' => 'success',
        ),
        Loader::map()
      );
    }

    /**
     * @covers Carica\Io\Loader
     */
    public function testReset() {
      Loader::map(
        array(
          'Sample\Foo\Bar' => 'fail',
          'Sample' => 'fail',
          'FooBar' => 'fail',
          'Sample\Foo' => 'success'
        )
      );
      Loader::reset();
      $this->assertEquals(
        array(),
        Loader::map()
      );
    }
  }
}