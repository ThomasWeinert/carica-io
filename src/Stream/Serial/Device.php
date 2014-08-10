<?php

namespace Carica\Io\Stream\Serial {

  class Device {

    private $_device = 0;
    private $_command = '';
    private $_baud = 57600;

    private $_baudRates = array (
      110 => 11,
      150 => 15,
      300 => 30,
      600 => 60,
      1200 => 12,
      2400 => 24,
      4800 => 48,
      9600 => 96,
      19200 => 19,
      38400 => 38400,
      57600 => 57600,
      115200 => 115200
    );


    public function __construct($device, $baud = 57600) {
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
        $pattern = '(^/dev/tty\.[^\s]+$)';
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