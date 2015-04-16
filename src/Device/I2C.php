<?php

namespace Carica\Io\Device {

  interface I2C
  {

    /**
     * Write data to the i2c device
     *
     * @param int $slaveAddress
     */
    function write($slaveAddress, $data);

    /**
     * Read data from the i2c device, this is an asynchronous call,
     * the callback is execute with the received data bytes.
     *
     * @param int $slaveAddress
     * @param int $length
     * @param callable $callback
     */
    function read($slaveAddress, $length, callable $callback);
  }
}