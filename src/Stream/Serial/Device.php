<?php

namespace Carica\Io\Stream\Serial {

  class Device {

    const BAUD_110 = 110;
    const BAUD_150 = 150;
    const BAUD_300 = 300;
    const BAUD_600 = 600;
    const BAUD_1200 = 1200;
    const BAUD_2400 = 2400;
    const BAUD_4800 = 4800;
    const BAUD_9600 = 9600;
    const BAUD_19200 = 19200;
    const BAUD_38400 = 38400;
    const BAUD_57600 = 57600;
    const BAUD_115200 = 115200;

    const BAUD_DEFAULT = self::BAUD_57600;

    private $_device = 0;
    private $_command = '';
    private $_baud = self::BAUD_DEFAULT;

    private $_baudRates = array (
      self::BAUD_110 => 11,
      self::BAUD_150 => 15,
      self::BAUD_300 => 30,
      self::BAUD_600 => 60,
      self::BAUD_1200 => 12,
      self::BAUD_2400 => 24,
      self::BAUD_4800 => 48,
      self::BAUD_9600 => 96,
      self::BAUD_19200 => 19,
      self::BAUD_38400 => 38400,
      self::BAUD_57600 => 57600,
      self::BAUD_115200 => 115200
    );


    public function __construct($device, $baud = self::BAUD_DEFAULT)
    {
      if (isset($this->_baudRates[$baud])) {
        $this->_baud = $baud;
      }
      if (substr(PHP_OS, 0, 3) === "WIN") {
        $pattern = '(^COM\d+:$)';
        $command = sprintf(
          'mode %s BAUD=%d PARITY=N data=8 stop=1 xon=off',
          strtolower($device),
          $this->_baudRates[$baud]
        );
      } elseif (substr(PHP_OS, 0, 6) === "Darwin") {
        $pattern = '(^/dev/(?:tty|cu)\.[^\s]+$)';
        $command = sprintf('stty -f %s speed %d', $device, $baud);
      } elseif (substr(PHP_OS, 0, 5) === "Linux") {
        $pattern = '(^/dev/tty\w+\d+$)';
        $command = sprintf('stty -F %s %d', $device, $baud);
      } else {
        throw new \LogicException(sprintf('Unsupported OS: "%s".', PHP_OS));
      }
      if (!preg_match($pattern, $device)) {
        throw new \LogicException(sprintf('Invalid serial port: "%s".', $device));
      }
      $this->_device = $device;
      $this->_command = $command;
    }

    public function getCommand() {
      return $this->_command;
    }

    public function getDevice() {
      return $this->_device;
    }

    public function getBaud() {
      return $this->_baud;
    }

    public function setUp() {
      exec($this->getCommand());
    }

    public function __toString() {
      return (string)$this->getDevice();
    }
  }
}
