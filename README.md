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
for non-blocking I/O.

***It's a learning project not a product. Use it at your own risk.***

Basics
------

The repository provides the API needed for non-blocking I/O. A
simple event loop and event emitter are included. The loop
implementation is not performance optimized, yet.

Firmata
-------

Originally a Firmata client library was part of this project. It is now an
separate project called [Carica Firmata](https://github.com/ThomasWeinert/carica-firmata)

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
