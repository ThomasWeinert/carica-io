<?php
declare(strict_types=1);

namespace Carica\Io\Device {

  /**
   * And interface representing a single hardware pin.
   */
  interface Pin {

    public const MODE_UNKNOWN = 0;
    public const MODE_INPUT = 1;
    public const MODE_OUTPUT = 2;
    public const MODE_ANALOG = 4;
    public const MODE_PWM = 8;
    public const MODE_SERVO = 16;
    public const MODE_SHIFT = 32;
    public const MODE_I2C = 64;

    /**
     * Set the pin mode
     *
     * @param int $mode
     */
    public function setMode(int $mode): void;

    /**
     * Return the current pin mode
     *
     * @return int
     */
    public function getMode(): int;

    /**
     * Set the pin value as a digital low/high
     *
     * @param bool $isHigh
     */
    public function setDigital(bool $isHigh);

    /**
     * Get the value of a digital pin
     * @return bool
     */
    public function getDigital(): bool;

    /**
     * Set the pin value as an float value between 0.0 and 1.0
     *
     * @param float $percent 0.0 - 1.0
     */
    public function setAnalog(float $percent);

    /**
     * @return float
     */
    public function getAnalog(): float;

    /**
     * Pin supports this mode
     *
     * @param int $mode
     * @return bool
     */
    public function supports(int $mode): bool;

    /**
     * Add a callback that is executed if the pin value changes
     *
     * @param callable $listener
     */
    public function onChange(callable $listener): void;
  }
}
