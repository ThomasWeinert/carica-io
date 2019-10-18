<?php
declare(strict_types=1);

namespace Carica\Io\Network\Http\Route {

  use Carica\Io\Event\Emitter as EventEmitter;
  use Carica\Io\Network\Http\Connection;
  use Carica\Io\Network\Http\Headers;
  use Carica\Io\Network\Http\Request;
  use Carica\Io\Network\Http\Response;
  use Carica\Io\Network\Http\Response as HTTPResponse;

  class WebSocket {

    use EventEmitter\Aggregation;

    public const EVENT_CLIENT_CONNECTED = 'client_connected';
    public const EVENT_CLIENT_DISCONNECTED = 'client_disconnected';
    public const EVENT_DATA = 'data';

    private const MAGIC = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    private $_clients = [];

    public function __construct(callable $onData = NULL) {
      if (NULL !== $onData) {
        $this->events()->on(self::EVENT_DATA, $onData);
      }
    }

    public function __invoke(Request $request): ?HTTPResponse {
      if ($key = $this->getWebSocketKey($request->headers)) {
        $response = $request->createResponse(new Response\Content\Text(''));
        $response->keepAlive = TRUE;
        $response->status = 101;
        $response->headers['Upgrade'] = 'websocket';
        $response->headers['Connection'] = 'Upgrade';
        $response->headers['Sec-WebSocket-Accept'] = base64_encode(
          sha1($key.self::MAGIC, TRUE)
        );
        $this->_clients[] = $request;
        $this->emitEvent(self::EVENT_CLIENT_CONNECTED, $request);
        $request->connection->events()->on(
          Connection::EVENT_READ_DATA,
          function ($data) use ($request) {
             $this->emitEvent(self::EVENT_DATA, $this->decode($data), $request);
          }
        );
        return $response;
      }
      return NULL;
    }

    /**
     * @param Headers $headers
     * @return string|null
     */
    private function getWebSocketKey(Headers $headers): ?string {
      /** @noinspection NotOptimalIfConditionsInspection */
      if (
        isset($headers['Upgrade'], $headers['Sec-WebSocket-Key']) &&
        (string)$headers['Upgrade'] === 'websocket'
      ) {
        return (string)$headers['Sec-WebSocket-Key'];
      }
      return NULL;
    }

    public function write(string $message): void {
      $encoded = $this->encode($message);
      foreach ($this->_clients as $index => $request) {
        if ($request->connection()->isActive()) {
          $request->connection()->write($encoded);
        } else {
          $this->emitEvent(self::EVENT_CLIENT_DISCONNECTED, $request);
          unset($this->_clients[$index]);
        }
      }
    }

    private function encode(string $value): string {
      $marker = 129; // FIN + text frame
      $length = strlen($value);
      if ($length < 126) {
        return pack('CC', $marker, $length).$value;
      }
      if ($length < 65536) {
        return pack('CCn', $marker, 126, $length).$value;
      }
      return pack('CCNN', $marker, 127, 0, $length).$value;
    }

    private function decode(string $value): string {
      $length = ord($value[1]) & 127;
      if ($length === 126) {
        $ofs = 8;
      } elseif ($length === 127) {
        $ofs = 14;
      } else {
        $ofs = 6;
      }
      $result = '';
      for ($i = $ofs, $c = strlen($value); $i < $c; $i++) {
        $result .= $value[$i] ^ $value[$ofs - 4 + ($i - $ofs) % 4];
      }
      return $result;
    }
  }
}
