# Transitive\Web

HTTP front controller and HTML-oriented view layer for Transitive.

This package builds on `transitive/core` and `transitive/routing` to provide browser-facing rendering, content negotiation, default HTML layout handling, and helpers for scripts, styles, and metadata.

[![Latest Stable Version](https://poser.pugx.org/transitive/web/v/stable?format=flat-square)](https://packagist.org/packages/transitive/web)
[![License](https://poser.pugx.org/transitive/web/license?format=flat-square)](https://packagist.org/packages/transitive/web)

## Installation
```sh
composer require transitive/web
```

PHP `8.1+` is required.

## What is included
- `Transitive\Web\Front`: an HTTP front controller with `Accept` header negotiation and response header management.
- `Transitive\Web\View`: a richer (than Transitive\Simple) view implementation with title, meta tag, stylesheet, and script helpers.

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

## Response formats
`Transitive\Web\Front` can emit different representations depending on the resolved content type, including:

- HTML and XHTML
- JSON
- XML
- YAML
- raw stylesheet content
- raw script content
- serialised document/head/content payloads

By default it inspects `$_SERVER['HTTP_ACCEPT']` and chooses the best supported MIME type.

## View helpers
`Transitive\Web\View` extends the simple core view with helpers such as:

- `addMetaTag()` / `addRawMetaTag()`
- `addStyle()` / `linkStyleSheet()` / `importStyleSheet()`
- `addScript()` / `linkScript()` / `importScript()`
- `getHead()`, `getMetas()`, `getStyles()`, and `getScripts()`

Example:

```php
<?php

use Transitive\Web\View;

$view = new View();
$view->setTitle('Home');
$view->addMetaTag('description', 'Example page');
$view->linkStyleSheet('/assets/site.css');
$view->linkScript('/assets/site.js', defer: true);
$view->addContent(function($data) {
	echo '<h1>Hello '.$data['name'].'</h1>';
}, 'text/html');
```

## License

[MIT](LICENSE)
