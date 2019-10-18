<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http {

  use Carica\Io\Event\Loop as EventLoop;
  use Carica\Io\Network\Server as NetworkServer;

  class Server extends NetworkServer {

    public const EVENT_CONNECTION_RECEIVED = 'connection';

    /**
     * @var callable
     */
    private $_route;

    public function __construct(EventLoop $loop, callable $route, string $address = 'tcp://0.0.0.0') {
      parent::__construct($loop, $address);
      $this->_route = $route;
    }

    public function listen(int $port = 8080): bool {
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
                    if (
                      !$response->keepAlive &&
                      ($connection = $response->connection())
                    ) {
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
