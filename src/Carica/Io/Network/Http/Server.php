<?php

namespace Carica\Io\Network\Http {

  use Carica\Io\Network;

  class Server extends Network\Server {

    private $_route = NULL;

    public function __construct(Callable $route, $address = 'tcp://0.0.0.0') {
      parent::__construct($address);
      $this->_route = $route;
    }

    public function listen($port = 8080) {
      $route = $this->_route;
      $this->events()->on(
        'connection',
        function ($stream) use ($route) {
          $request = new Connection($stream);
          $request->events()->on(
            'request',
            function ($request) use ($route) {
              echo $request->method.' '.$request->url."\n";
              if (!($response = $route($request))) {
                $response = new Response\Error(
                  $request, 404
                );
              }
              $response
                ->send()
                ->always(
                  function () use ($response) {
                    $response->connection()->close();
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