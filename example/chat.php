<?php
/**
 * A really simple chat server. Clients can connect to it using netcat
 *
 * Basically a php implementation of
 * http://dhotson.tumblr.com/post/271733389/a-simple-chat-server-in-node-js
 */

class Client {
  public $connection = NULL;
  public $nick = NULL;
}

function broadcast($clients, $data) {
  foreach ($clients as $recipient) {
    if ($recipient->connection->isActive()) {
      $recipient->connection->write($data);
    }
  }
}

include('../src/Io/Loader.php');
Carica\Io\Loader::register();
use Carica\Io;

$clients = array();

$server = new Io\Network\Server();
$server->events()->on(
  'connection',
  function ($stream) use (&$clients) {
    echo "Client connected: $stream\n";
    $client = new Client();
    $client->connection = new Io\Network\Connection($stream);
    $client->connection->write("Welcome, enter your username:\n");
    $client->connection->events()->on(
      'data',
      function($data) use ($client, &$clients) {
        if (empty($client->name) &&
            preg_match("(\S+)", $data, $matches) &&
            !isset($clients[$matches[0]])) {
          $client->name = $matches[0];
          $clients[$client->name] = $client;
          $client->connection->write(str_repeat('=', 72)."\n");
          broadcast($clients, $client->name." has joined.\n");
          echo $client->name." has joined.\n";
          return;
        }
        if (!empty($client->name)) {
          if (preg_match('(^/(?P<command>.*))', $data, $matches)) {
            switch ($matches['command']) {
            case 'users' :
              foreach ($clients as $user) {
                $client->connection->write('- '.$user->name."\n");
              }
              break;
            case 'quit' :
              $client->connection->close();
              break;
            default :
              $client->connection->write("Unknown command.\n");
            }
            return;
          }
          broadcast($clients, $client->name.': '.$data);
        }
      }
    );

    $client->connection->events()->once(
      'close',
      function () use ($client, &$clients) {
        unset($clients[$client->name]);
        broadcast($clients, $client->name." has left\n");
        echo $client->name." has left.\n";
      }
    );
  }
);

$server->listen(7000);

$loop->run();