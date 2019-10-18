<?php
declare(strict_types=1);

namespace Carica\Io {

  use PHPUnit\Framework\TestCase;

  include_once(__DIR__.'/Bootstrap.php');

  class StreamTest extends TestCase {

    public function setUp(): void {
      class_exists(Stream::class);
    }

    public function testEncodeBinaryFromArray(): void {
      $bytes = new ByteArray();
      $bytes->fromHexString('FFF1F2', TRUE);
      $this->assertEquals(
        (string)$bytes,
        encodeBinaryFromArray([0xFF, 0xF1, 0xF2])
      );
    }

    public function testDecodeBinaryToArray(): void {
      $bytes = new ByteArray();
      $bytes->fromHexString('FFF1F2', TRUE);
      $this->assertEquals(
        [0xFF, 0xF1, 0xF2],
        decodeBinaryToArray((string)$bytes)
      );
    }
  }
}
