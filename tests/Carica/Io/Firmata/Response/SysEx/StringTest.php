<?php

namespace Carica\Io\Firmata\Response\SysEx {

  include_once(__DIR__.'/../../../Bootstrap.php');

  use Carica\Io;
  use Carica\Io\Firmata;

  class StringTest extends \PHPUnit_Framework_TestCase {

    public function testConstructor() {
      $bytes = new Io\ByteArray();
      $bytes->fromHexString('71480061006c006c006f002000570065006c007400', TRUE);
      $string = new String(0x71, iterator_to_array($bytes));
      $this->assertEquals(
        'Hallo Welt',
        $string->text
      );
    }
  }
}