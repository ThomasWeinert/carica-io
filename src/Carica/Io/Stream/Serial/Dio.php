<?php

namespace Carica\Io\Stream\Serial {

  use Carica\Io;
  use Carica\Io\Event;

  class Dio implements Io\Stream
  {

    use Event\Emitter\Aggregation;
    use Event\Loop\Aggregation;

    private $_device = 0;
    private $_resource = NULL;
    private $_listener = NULL;

    public function __construct($device, $baud = 57600)
    {
      $this->_device = new Device($device, $baud);
    }

    public function __destruct()
    {
      $this->close();
    }

    public function resource($resource = NULL)
    {
      if ($resource === FALSE) {
        $this->_resource = NULL;
      } elseif (isset($resource)) {
        $this->_resource = $resource;
        $that = $this;
        $this->_listener = $this->loop()->setInterval(
          function() use ($that) {
            $that->read();
          },
          50
        );
      }
      if (is_resource($this->_resource)) {
        return $this->_resource;
      } elseif (isset($this->_listener)) {
        $this->loop()->remove($this->_listener);
        $this->_listener = NULL;
      }
      return NULL;
    }

    public function open()
    {
      $device = 'dio.serial://' . $this->_device;
      $context = $this->createContext();

      if ($resource = fopen($device, 'r+', FALSE, $context)) {
        $this->resource($resource);
        return TRUE;
      }

      $this->events()->emit('error', sprintf('Can not open serial port: "%s".', $device));
      return FALSE;

    }

    private function createContext()
    {
      return stream_context_create(
        array(
          'dio' =>
          array(
            'data_rate' => $this->_device->getBaud(),
            'data_bits' => 8,
            'stop_bits' => 1,
            'parity' => 0,
            'flow_control' => 0,
            'is_blocking' => 0,
            'canonical' => 1
          )
        )
      );

    }

    public function close()
    {
      if ($resource = $this->resource()) {
        $this->resource(FALSE);
        fclose($resource);
      }
    }

    public function read($bytes = 1024)
    {
      if ($resource = $this->resource()) {
        $data = fread($resource, $bytes);
        if (is_string($data) && $data !== '') {
          $this->events()->emit('read-data', $data);
          return $data;
        }
      }
      return NULL;
    }

    public function write($data)
    {
      if ($resource = $this->resource()) {
        fwrite(
          $resource,
          $writtenData = is_array($data) ? Io\encodeBinaryFromArray($data) : $data
        );
        $this->events()->emit('write-data', $writtenData);
      }
    }
  }
}
