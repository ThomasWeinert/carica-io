<?php
declare(strict_types=1);

namespace Carica\Io\Device {

  use Carica\Io\Deferred\PromiseLike;

  /**
   * Interface I2C Device
   *
   * This represents an i2c device, the address needs to be stored by the object. It should be
   * one of the constructor arguments.
   */
  interface I2C {

    /**
     * Write data to the i2c device
     * @param array $data
     */
    public function write(array $data): void;

    /**
     * Read data from the i2c device, this is an asynchronous call,
     * the promise is resolved with the received data bytes.
     *
     * @param int $length
     * @return PromiseLike
     */
    public function read($length): PromiseLike;

    /**
     * Start continuous reading, repeatedly calls the listener
     *
     * @param int $length
     * @param callable $listener
     */
    public function startReading($length, callable $listener): void;

    /**
     * Stop continuous reading on the specified address
     */
    public function stopReading(): void;
  }
}
