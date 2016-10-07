<?php

namespace Carica\Io\Device {

  /**
   * Interface I2C Device
   *
   * This represents an i2c device, the address needs to be stored by the object. It should be
   * one of the constructor arguments.
   */
  interface I2C
  {

    /**
     * Write data to the i2c device
     * @param array $data
     */
    function write(array $data);

    /**
     * Read data from the i2c device, this is an asynchronous call,
     * the callback is execute with the received data bytes.
     *
     * @param int $length
     * @return \Carica\Io\Deferred\Promise
     */
    function read($length);

    /**
     * Start continuous reading, repeatingly calls the listener
     *
     * @param int $length
     * @param callable $listener
     */
    function startReading($length, callable $listener);

    /**
     * Stop continuous reading on the specified address
     */
    function stopReading();
  }
}