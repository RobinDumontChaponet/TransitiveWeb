# Transitive\Web

[![Latest Stable Version](https://poser.pugx.org/transitive/web/v/stable?format=flat-square)](https://packagist.org/packages/transitive/web)
[![License](https://poser.pugx.org/transitive/web/license?format=flat-square)](https://packagist.org/packages/transitive/web)
[![Build Status](https://travis-ci.org/RobinDumontChaponet/TransitiveWeb.svg?branch=master)](https://travis-ci.org/RobinDumontChaponet/TransitiveWeb)
[![Coverage Status](https://coveralls.io/repos/github/RobinDumontChaponet/TransitiveWeb/badge.svg)](https://coveralls.io/github/RobinDumontChaponet/TransitiveWeb)

## Installation

```sh
composer require transitive/web
```

## Basic Usage

```php
<?php

use Transitive\Web;
use Transitive\Routing;

require __DIR__.'/../vendor/autoload.php';

$front = new Web\WebFront();

$front->addRouter(new Routing\PathRouter(dirname(dirname(__FILE__)).'/presenters', dirname(dirname(__FILE__)).'/views'));

$front->execute(@$_GET['request'] ?? 'index');

echo $front;

```

## License

The MIT License (MIT)

Copyright (c) 2016 Robin Dumont-Chaponet

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
