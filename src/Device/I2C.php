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
     * @return \Carica\Io\Deferred\Promise
     */
    function read($slaveAddress, $length);



    /**
     * Start continuous reading, repeatingly calls the listener
     *
     * @param int $slaveAddress
     * @param int $length
     * @param callable $listener
     */
    function startReading($slaveAddress, $length, callable $listener);

    /**
     * Stop continuous reading on the specified address
     *
     * @param int $slaveAddress
     */
    function stopReading($slaveAddress);
  }
}