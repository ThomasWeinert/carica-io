Carica Io
=========

[![Build Status](https://travis-ci.org/ThomasWeinert/carica-io.svg?branch=master)](https://travis-ci.org/ThomasWeinert/carica-io)
[![License](https://poser.pugx.org/carica/io/license.svg)](https://packagist.org/packages/carica/io)
[![Total Downloads](https://poser.pugx.org/carica/io/downloads.svg)](https://packagist.org/packages/carica/io)
[![Latest Stable Version](https://poser.pugx.org/carica/io/v/stable.svg)](https://packagist.org/packages/carica/io)
[![Latest Unstable Version](https://poser.pugx.org/carica/io/v/unstable.svg)](https://packagist.org/packages/carica/io)

License:   [The MIT License](http://www.opensource.org/licenses/mit-license.php)

Copyright: 2013-2019 Thomas Weinert <thomas@weinert.info>

Carica Io is a collection of experimental php classes and scripts
for non-blocking I/O. It provides the basic building blocks for 
hardware control using Firmata (Arduino) and GPIO (Raspberry PI).

Basics
------

The repository provides the API needed for non-blocking I/O. A
simple event loop and event emitter are included. The loop
implementation is not performance optimized. However it is possible to use
an adapter for ReactPHP or AMPHP.

It includes an (incomplete) HTTP server that should be enough for the first steps. 
It supports GET requests and WebSocket connections.  

Related Projects
----------------

```plaintext
           +---------------+
           |  Carica\Chip  |
           +-------+-------+
                   ^
                   |
        +----------+----------+
        |                     |
+-------+-------+    +--------+-------+
|  Carica\GPIO  |    | Carica\Firmata |
+-------+-------+    +--------+-------+
        ^                     ^
        |                     |
        +----------+----------+
                   |
           +-------+-------+
           |   Carica\Io   |
           +---------------+
```

*Carica/Io* provides the classes for event based programming and a simple web server.
It defines interfaces for hardware control (Pin, ShiftOut, ...). 
[Carica/Firmata](https://github.com/ThomasWeinert/carica-firmata) is a client library 
for the Firmata protocol it allows to control 
Arduino boards over a serial or a network connection. [Carica/GPIO](https://github.com/ThomasWeinert/carica-gpio) implements GPIO
into PHP using the file system or the WiringPI library for the Raspberry PI.

[Carica/Chip](https://github.com/ThomasWeinert/carica-chip) implements classes for hardware objects. Like a LED, a RGB-LED, a Servo,...

Usage
-----

You will need to get the loop instance from the factory, 
attach events and run it.

```php
$loop = Carica\Io\Event\Loop\Factory::get();
$loop->setInterval(
  static function () {
    static $i = 0;
    echo $i++;
  },
  1000
);
$loop->run();
```
