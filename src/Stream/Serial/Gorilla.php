<?php

namespace Carica\Io\Stream\Serial {

  use Carica\Io;
  use Carica\Io\Event;
  use PHPMake\SerialPort;

  class Gorilla
    implements
    Io\Stream,
    Io\Event\HasLoop
  {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_device = 0;
    private $_port = NULL;
    private $_listener = NULL;

    public function __construct($device, $baud = 57600)
    {
      $this->_device = new Device($device, $baud);
    }

    public function __destruct()
    {
      $this->close();
    }

    public function port($port = NULL)
    {
      if ($port === FALSE) {
        $this->_port = NULL;
      } elseif (isset($port)) {
        $this->_port = $port;
        $that = $this;
        $this->_listener = $this->loop()->setInterval(
          function () use ($that) {
            $that->read();
          },
          50
        );
      }
      if ($this->_port instanceof SerialPort) {
        return $this->_port;
      } elseif (isset($this->_listener)) {
        $this->loop()->remove($this->_listener);
        $this->_listener = NULL;
      }
      return NULL;
    }

    public function isOpen()
    {
      return $this->port() instanceof SerialPort;
    }

    public function open()
    {
      var_dump($this->_device->getDevice());
      if ($port = new SerialPort($this->_device->getDevice())) {
        $port
          ->setFlowControl(SerialPort::FLOW_CONTROL_SOFT)
          ->setBaudRate($this->_device->getBaud())
          ->setCanonical(false)
          ->setVTime(1)
          ->setVMin(0);
        $this->port($port);
        return TRUE;
      }

      $this->events()->emit(
        'error',
        sprintf('Can not open serial port: "%s".', $this->_device->getDevice())
      );
      return FALSE;

    }

    public function close()
    {
      if ($port = $this->port()) {
        $this->port(FALSE);
        $port->close();
      }
    }

    public function read($bytes = 1024)
    {
      if ($port = $this->port()) {
        $data = $port->read($bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data)
    {
      if ($port = $this->port()) {
        $port->write(
          $writtenData = is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
      }
    }
  }
}
