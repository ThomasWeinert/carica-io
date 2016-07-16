<?php

namespace Carica\Io\Device {

  interface Pin
  {

    const MODE_UNKNOWN = 0;
    const MODE_INPUT = 1;
    const MODE_OUTPUT = 2;
    const MODE_ANALOG = 4;
    const MODE_PWM = 8;
    const MODE_SERVO = 16;
    const MODE_SHIFT = 32;
    const MODE_I2C = 64;

    const MODE_LABELS = [
      self::MODE_UNKNOWN => 'unknown',
      self::MODE_INPUT => 'input',
      self::MODE_OUTPUT => 'output',
      self::MODE_ANALOG => 'analog',
      self::MODE_PWM => 'pwm',
      self::MODE_SHIFT => 'shift',
      self::MODE_I2C => 'i2c',
    ];

    /**
     * Set the pin mode
     *
     * @param int $mode
     */
    function setMode($mode);

    /**
     * Return the current pin mode
     *
     * @return int
     */
    function getMode();

    /**
     * Set the pin value as a digital low/high
     *
     * @param bool $isHigh
     */
    function setDigital($isHigh);

    /**
     * Get the value of a digital pin
     * @return bool
     */
    function getDigital();

    /**
     * Set the pin value as an float value between 0.0 and 1.0
     *
     * @param float $percent 0.0 - 1.0
     */
    function setAnalog($percent);

    /**
     * @return float
     */
    function getAnalog();

    /**
     * Pin supports this mode
     *
     * @param int $mode
     * @return bool
     */
    function supports($mode);

    /**
     * Add a callback that is executed if the pin value changes
     *
     * @param callable $listener
     */
    function onChange(callable $listener);
  }
}