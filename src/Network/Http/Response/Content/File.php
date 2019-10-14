<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Response\Content {

  use Carica\Io;
  use Carica\Io\Network;
  use Carica\Io\Network\Http\Response;

  class File
    extends
      Response\Content
    implements
      Io\Event\HasLoop,
      Io\File\HasAccess {

    use Io\File\Access\Aggregation;
    use Io\Event\Loop\Aggregation;

    private $_filename;
    private $_bufferSize = 51200;

    public function __construct(string $filename, string $type = 'application/octet-stream', string $encoding = '') {
      parent::__construct($type, $encoding);
      $this->_filename = (string)$filename;
    }

    public function sendTo(Network\Connection $connection) {
      if ($file = $this->fileAccess()->getFileResource($this->_filename)) {
        $defer = new Io\Deferred();
        $bytes = $this->_bufferSize;
        $interval = $this->loop()->setInterval(
          static function () use ($file, $bytes, $defer, $connection) {
            if (is_resource($file) && $connection->isActive()) {
              if (feof($file)) {
                $defer->resolve();
              } else {
                $connection->write(fread($file, $bytes));
                return;
              }
            } else {
              $defer->reject();
            }
          },
          100
        );
        $defer->always(
          function () use ($interval) {
            $this->loop()->remove($interval);
          }
        );
        return $defer->promise();
      }
      return FALSE;
    }

    public function getLength(): int {
      return $this->fileAccess()->getInfo($this->_filename)->getSize();
    }
  }
}
