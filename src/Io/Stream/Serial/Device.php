<?php

namespace Carica\Io\Stream {

  class Device {
    
    private $_device = 0;
    private $_command = '';
    
    public function __construct($device) {
      if (substr(PHP_OS, 0, 3) === "WIN") {
        $pattern = '(^COM\d+:$)';
        $command = sprintf(
            'mode %s BAUD=57600 PARITY=N data=8 stop=1 xon=off', strtolower($device)
        );
      } elseif (substr(PHP_OS, 0, 6) === "Darwin") {
        $pattern = '(^COM\d+:$)';
        $command = sprintf('stty -F %s', $device);
      } elseif (substr(PHP_OS, 0, 5) === "Linux") {
        $pattern = '(^/dev/tty\w+\d+$)';
        $prepare = sprintf('stty -F %s', $device);
      } else {
        throw new LogicException(sprintf('Unsupport OS: "%s".', PHP_OS));
      }
      if (!preg_match($pattern, $device)) {
        throw new LogicException(sprintf('Invalid serial port: "%s".', $device));
      }
      $this->_device = $device;
      $this->_command = $command;
    }
    
    public function setUp() {
      exec($this->_command);
    }
    
    public function __toString() {
      return $this->_device;
    }
  }
}