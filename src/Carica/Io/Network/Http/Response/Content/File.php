<?php

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io;
  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  class File extends Response\Content {

    use Io\FileSystem\Aggregation;
    use Io\Event\Loop\Aggregation;

    private $_filename = NULL;
    private $_bufferSize = 51200;

    public function __construct($filename, $type = 'application/octet-stream') {
      parent::__construct($type);
      $this->_filename = (string)$filename;
    }

    public function sendTo(Network\Connection $connection) {
      if ($file = $this->fileSystem()->getFileResource($this->_filename)) {
        $defer = new Io\Deferred();
        $bytes = $this->_bufferSize;
        $this->loop()->setInterval(
          function () use ($file, $bytes, $defer, $connection) {
            if ($connection->isActive() && is_resource($file)) {
              if (feof($file)) {
                $defer->resolve();
              } else {
                $connection->write(fread($file, $bytes));
              }
            } else {
              $defer->reject();
            }
          },
          100
        );
        return $defer->promise();
      }
      return FALSE;
    }

    public function getLength() {
      return $this->fileSystem()->getInfo($this->_filename)->getSize();
    }
  }
}
