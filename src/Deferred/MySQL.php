<?php

namespace Carica\Io\Deferred {

  use Carica\Io;
  use Carica\Io\Event;

  class MySQL {

    use Event\Loop\Aggregation;

    /**
     * @var \mysqli
     */
    private $_mysqli = NULL;

    public function __construct($mysqli) {
      $this->_mysqli = $mysqli;
    }

    public function __invoke() {
      return call_user_func_array(array($this, 'query'), func_get_args());
    }

    public function query($sql) {
      $defer = new Io\Deferred();
      $mysqli = $this->_mysqli;
      $mysqli->query($sql, MYSQLI_ASYNC);
      $this->loop()->setInterval(
        function() use ($defer, $mysqli) {
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