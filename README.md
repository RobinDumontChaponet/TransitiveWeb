# Transitive\Front

[![Latest Stable Version](https://poser.pugx.org/transitive/front/v/stable?format=flat-square)](https://packagist.org/packages/transitive/front)
[![License](https://poser.pugx.org/transitive/front/license?format=flat-square)](https://packagist.org/packages/transitive/front)
[![Build Status](https://travis-ci.org/RobinDumontChaponet/TransitiveFront.svg?branch=next)](https://travis-ci.org/RobinDumontChaponet/TransitiveFront)
[![Coverage Status](https://coveralls.io/repos/github/RobinDumontChaponet/TransitiveFront/badge.svg)](https://coveralls.io/github/RobinDumontChaponet/TransitiveFront)

## Installation

```sh
composer require transitive/front
```

## Basic Usage

```php
<?php
use Transitive\Front;
use Transitive\Core\Route;

require __DIR__.'/../vendor/autoload.php';

$front = new Transitive\Front\WebFront();

$front->addRouter(new Transitive\Front\PathRouter(dirname(dirname(__FILE__)).'/presenters', dirname(dirname(__FILE__)).'/views'));

$request = @$_GET['request'];

$front->execute($request ?? 'index');

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
