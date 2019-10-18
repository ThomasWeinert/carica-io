<?php
declare(strict_types=1);

namespace Carica\Io\Device {

  interface ShiftOut {

    /**
     * Write data using shift out. This will call begin(), transfer($data), end()
     *
     * @param int|string|int[] $data
     * @param bool $isBigEndian
     */
    public function write($data, bool $isBigEndian = TRUE): void;

    /**
     * Start transfer
     */
    public function begin(): void;

    /**
     * Transfer data
     *
     * @param int|string|int[] $data
     * @param bool $isBigEndian
     */
    public function transfer($data, bool $isBigEndian = TRUE): void;

    /**
     * End transfer
     */
    public function end(): void;
  }
}
