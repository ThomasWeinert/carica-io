<?php

namespace Carica\Io\Device {

  interface ShiftOut
  {

    /**
     * Write data using shift out. This will call begin(), transfer($data), end()
     *
     * @param int|string|int[] $data
     * @param bool $isBigEndian
     */
    function write($data, $isBigEndian = TRUE);

    /**
     * Start transfer
     */
    function begin();

    /**
     * Transfer data
     *
     * @param int|string|int[] $data
     * @param bool $isBigEndian
     */
    function transfer($data, $isBigEndian = TRUE);

    /**
     * End transfer
     */
    function end();
  }
}