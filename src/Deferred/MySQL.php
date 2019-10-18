<?php
declare(strict_types=1);

namespace Carica\Io\Deferred {

  use Carica\Io\Deferred;
  use Carica\Io\Event\HasLoop as HasEventLoop;
  use Carica\Io\Event\Loop as EventLoop;
  use mysqli;

  class MySQL implements HasEventLoop {

    use EventLoop\Aggregation;

    /**
     * @var mysqli
     */
    private $_mysqli;

    /**
     * @param EventLoop $loop
     * @param mysqli $mysqli
     */
    public function __construct(EventLoop $loop, $mysqli) {
      $this->loop($loop);
      $this->_mysqli = $mysqli;
    }

    public function __invoke(string $sql): PromiseLike {
      $defer = new Deferred();
      $mysqli = $this->_mysqli;
      $mysqli->query($sql, MYSQLI_ASYNC);
      $this->loop()->setInterval(
        static function() use ($defer, $mysqli) {
          $links = $errors = $reject = array($mysqli);
          if ($mysqli->poll($links, $errors, $reject, 0, 0)) {
            if ($result = $mysqli->reap_async_query()) {
              $defer->resolve($result);
            } else {
              $defer->reject();
            }
          }
        },
        50
      );
      return $defer->promise();
    }
  }
}
