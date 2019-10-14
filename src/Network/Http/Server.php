<?php

namespace Carica\Io\Network\Http {

  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\Network;

  class Server extends Network\Server {

    public const EVENT_CONNECTION_RECEIVED = 'connection';

    /**
     * @var callable
     */
    private $_route;

    public function __construct(EventLoop $loop, Callable $route, $address = 'tcp://0.0.0.0') {
      parent::__construct($loop, $address);
      $this->_route = $route;
    }

    public function listen($port = 8080) {
      $route = $this->_route;
      $this->events()->on(
        self::EVENT_CONNECTION_RECEIVED,
        function ($stream) use ($route) {
          $request = new Connection($this->loop(), $stream);
          $request->events()->on(
            Connection::EVENT_REQUEST_RECEIVED,
            static function ($request) use ($route) {
              echo $request->method.' '.$request->url."\n";
              if (!($response = $route($request))) {
                $response = new Response\Error(
                  $request, 404
                );
              }
              $response
                ->send()
                ->always(
                  static function () use ($response) {
                    if ($connection = $response->connection()) {
                      $connection->close();
                    }
                  }
                );
            }
          );
        }
      );
      return parent::listen($port);
    }
  }
}
