<?php

namespace Carica\Io\Device {

  interface ShiftOut
  {

    /**
     * Write data using shift out. This will call begin(), transfer($data), end()
     *
     * @param int|string|int[] $data
     */
    function write($data);

    /**
     * Start transfer
     */
    function begin();

    /**
     * Transfer data
     *
     * @param int|string|int[] $data
     */
    function transfer($data);

    /**
     * End transfer
     */
    function end();
  }
}