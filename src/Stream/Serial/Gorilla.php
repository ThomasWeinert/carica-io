<?php

namespace Carica\Io\Stream\Serial {

  use Carica\Io;
  use Carica\Io\Event;
  use PHPMake\SerialPort;

  class Gorilla
    implements
    Io\Stream,
    Io\Event\HasLoop {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_device;
    private $_port;
    private $_listener;

    /**
     * Gorilla constructor.
     * @param string $device
     * @param int $baud
     * @throws \LogicException
     */
    public function __construct(string $device, int $baud = Device::BAUD_DEFAULT) {
      $this->_device = new Device($device, $baud);
    }

    public function __destruct() {
      $this->close();
    }

    public function port($port = NULL): ?SerialPort {
      if ($port === FALSE) {
        $this->_port = NULL;
      } elseif ($port instanceof SerialPort) {
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
      }
      if (NULL !== $this->_listener) {
        $this->loop()->remove($this->_listener);
        $this->_listener = NULL;
      }
      return NULL;
    }

    public function isOpen(): bool {
      return $this->port() instanceof SerialPort;
    }

    public function open(): bool {
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

    public function close() {
      if ($port = $this->port()) {
        $this->port(FALSE);
        $port->close();
      }
    }

    public function read(int $bytes = 1024): ?string {
      if ($port = $this->port()) {
        $data = $port->read($bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data): bool {
      if ($port = $this->port()) {
        $port->write(
          $writtenData = is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
        return TRUE;
      }
      return FALSE;
    }
  }
}
