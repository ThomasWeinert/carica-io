<?php

namespace Carica\Io {

  interface Stream extends Event\HasEmitter {

    public const EVENT_READ_DATA = 'read-data';
    public const EVENT_WRITE_DATA = 'write-data';
    public const EVENT_ERROR = 'error';

    public function isOpen(): bool;

    public function open(): bool;

    public function close();

    public function read(int $bytes = 1024): ?string;

    public function write($data): bool;

  }

  function encodeBinaryFromArray(array $data): string {
    array_unshift($data, 'C*');
    return pack(...$data);
  }

  function decodeBinaryToArray(string $data): array {
    return array_slice(unpack('C*', "\0".$data), 1);
  }

}
