<?php

namespace Carica\Io\Firmata {

  include_once(__DIR__.'/../Bootstrap.php');

  class BufferTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {
      class_exists('Carica\Io\Firmata\Board');
    }

    /**
     * @covers Carica\Io\Firmata\Buffer
     * @dataProvider provideDataWithVersionAtEnd
     */
    public function testRecievesVersion($data) {
      $buffer = new Buffer();
      $buffer->addData(
        $this->getBinaryStringFromHex($data)
      );
      $this->assertAttributeSame(TRUE, '_versionReceived', $buffer);
    }

    private function getBinaryStringFromHex($string) {
      $byteArray = new \Carica\Io\ByteArray();
      $byteArray->fromHexString($string, TRUE);
      return (string)$byteArray;
    }

    public function provideDataWithVersionAtEnd() {
      return array(
        'simply version byte' => array('F9'),
        'null bytes up front' => array('0000F9'),
        'garbage up front' => array('010203F9')
      );
    }
  }
}