<?php
declare(strict_types=1);

namespace Carica\Io\Network\HTTP\Response\Content {

  use Carica\Io\Deferred;
  use Carica\Io\Event\HasLoop as HasEventLoop;
  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\File\Access as FileAccess;
  use Carica\Io\File\HasAccess as HasFileAccess;
  use Carica\Io\Network\Connection as NetworkConnection;
  use Carica\Io\Network\HTTP\Response\Content as ResponseContent;

  class File
    extends
      ResponseContent
    implements
      HasEventLoop,
      HasFileAccess {

    use FileAccess\Aggregation;
    use EventLoop\Aggregation;

    private $_filename;
    private $_bufferSize = 51200;

    public function __construct(string $filename, string $type = 'application/octet-stream', string $encoding = '') {
      parent::__construct($type, $encoding);
      $this->_filename = $filename;
    }

    public function sendTo(NetworkConnection $connection) {
      if ($file = $this->fileAccess()->getFileResource($this->_filename)) {
        $defer = new Deferred();
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
