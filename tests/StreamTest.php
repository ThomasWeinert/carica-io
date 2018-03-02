<?php

namespace Carica\Io {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/Bootstrap.php');

  class StreamTest extends TestCase {

    public function setUp() {
      class_exists(\Carica\Io\Stream::class);
    }

    function testEncodeBinaryFromArray() {
      $bytes = new ByteArray();
      $bytes->fromHexString('FFF1F2', TRUE);
      $this->assertEquals(
        (string)$bytes,
        \Carica\Io\encodeBinaryFromArray([0xFF, 0xF1, 0xF2])
      );
    }

    function testDecodeBinaryToArray() {
      $bytes = new ByteArray();
      $bytes->fromHexString('FFF1F2', TRUE);
      $this->assertEquals(
        [0xFF, 0xF1, 0xF2],
        \Carica\Io\decodeBinaryToArray((string)$bytes)
      );
    }
  }
}