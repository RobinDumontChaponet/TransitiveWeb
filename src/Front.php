<?php

namespace Transitive\Web;

use Transitive\Core;
use Transitive\Simple;
use Transitive\Routing;

function getBestSupportedMimeType($mimeTypes = null) {
    // Values will be stored in this array
    $acceptTypes = array();
    // divide it into parts in the place of a ","
    $accept = explode(',', strtolower(str_replace(' ', '', $_SERVER['HTTP_ACCEPT'])));
    foreach ($accept as $a) {
        // the default quality is 1.
        $q = 1;
        // check if there is a different quality
        if (strpos($a, ';q=')) {
            // divide "mime/type;q=X" into two parts: "mime/type" i "X"
            list($a, $q) = explode(';q=', $a);
        }
        // mime-type $a is accepted with the quality $q
        // WARNING: $q == 0 means, that mime-type isn’t supported!
        $acceptTypes[$a] = $q;
    }
    arsort($acceptTypes);
    // if no parameter was passed, just return parsed data
    if (!$mimeTypes) return $acceptTypes;
    $mimeTypes = array_map('strtolower', (array) $mimeTypes);
    // let’s check our supported types:
    foreach ($acceptTypes as $mime => $q) {
       if ($q && in_array($mime, $mimeTypes)) return $mime;
    }
    // no mime-type found
    return null;
}

/**
 * WebFront class.
 *
 * @extends Simple\Front
 * @implements Routing\FrontController
 */
class Front extends Simple\Front implements Routing\FrontController
{
    private $httpErrorRoute;
    private static $defaultHttpErrorRoute;

    private $contentType;
    public static $mimeTypes = array(
        'application/xhtml+xml', 'text/html',
        'application/json', 'application/xml',
        'application/vnd.transitive.content+xhtml', 'application/vnd.transitive.content+html',
        'application/vnd.transitive.content+css', 'application/vnd.transitive.content+javascript',
        'application/vnd.transitive.content+json', 'application/vnd.transitive.content+xml', 'application/vnd.transitive.content+yaml',
        'application/vnd.transitive.head+json', 'application/vnd.transitive.head+xml', 'application/vnd.head+yaml',
        'application/vnd.transitive.document+json', 'application/vnd.transitive.document+xml', 'application/vnd.transitive.document+yaml',
        'text/plain',
    );

    public const defaultViewClassName = '\Transitive\Web\View';

    public function __construct()
    {
        $this->contentType = getBestSupportedMimeType(self::$mimeTypes);
        $this->obClean = true;
        $this->obContent = '';

        $this->layout = new Routing\Route(new Core\Presenter(), new Simple\View());

        $this->setLayoutContent(function ($data) { ?><!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<?= $data['view']->getMetas(); ?>
<?= $data['view']->getTitle('Default layout'); ?>
<?= $data['view']->getStyles(); ?>
<?= $data['view']->getScripts(); ?>
</head>
<body>
	<?= $data['view']; ?>
</body>
</html><?php
        });
    }

    /*
     * @todo remove this ?
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    protected function _getRoute(string $query, string $defaultViewClassName = null): ?Routing\Route
    {
        try {
            return parent::_getRoute($query, $defaultViewClassName);
        } catch(Routing\RoutingException $e) {
            if($e->getCode() > 200) {
                http_response_code($e->getCode());
                $_SERVER['REDIRECT_STATUS'] = $e->getCode();
            }

            $route = $this->httpErrorRoute ?? self::$defaultHttpErrorRoute;
            if(isset($route))
                $route->addExposedVariable('query', $query);

            return $route ?? null;
        }
    }

    public function execute(string $queryURL = null, bool $sendHeaders = true): ?Routing\Route
    {
        $this->route = $this->_getRoute($queryURL, self::defaultViewClassName);

        if(isset($this->route)) {
            try {
                $this->obContent = $this->route->execute($this->obClean);
                $this->executed = true;
            } catch(Routing\RoutingException $e) {
                if($sendHeaders && $e->getCode() > 200) {
                    http_response_code($e->getCode());
                    $_SERVER['REDIRECT_STATUS'] = $e->getCode();
                }
            } catch(Core\BreakFlowException $e) {
                return $this->execute($e->getQueryURL(), $sendHeaders);
            }

            if($sendHeaders) {
                if(/* !$this->route->hasView() || */($this->route->hasView() && !$this->route->getView()->hasContent())) {
                    http_response_code(204);
                    $_SERVER['REDIRECT_STATUS'] = 204;
                }
                if(!empty($this->contentType)) {
                    header('Content-Type: '.$this->contentType);
                    if(!in_array($this->contentType, array('application/xhtml+xml', 'text/html', 'plain/text'))) {
                        header('Expires: '.gmdate('D, d M Y H:i:s').' GMT');
                        header('Cache-Control: public, max-age=60');
                    }
                }
                header('Vary: X-Requested-With,Content-Type');
            }

            $this->layout->getPresenter()->add('view', $this->route->getView());
            $this->layout->execute($this->obClean);
        }

        return $this->route;
    }

    /**
     * @codeCoverageIgnore
     */
    public function __debugInfo()
    {
        return [
            'httpErrorRoute' => $this->httpErrorRoute,
            'routers' => $this->routers,
            'route' => $this->route,
            'obClean' => $this->obClean,
            'obContent' => $this->obContent,
            'executed' => $this->executed,
        ];
    }

    /**
     * @codeCoverageIgnore
     */
    public function __toString(): string
    {
        return $this->getContent();
    }

    /**
     * Return processed content from current route.
     *
     * @return string
     *
     * @param string $contentType = null
     * @param string $key         = null
     */
    public function getContent(string $contentType = null, string $contentKey = null): string
    {
        if(empty($this->route)) {
            http_response_code(404);
            $_SERVER['REDIRECT_STATUS'] = 404;

            return 'No Route';
        }

        if(null == $contentType)
            $contentType = $this->contentType;
        switch($contentType) {
            case 'application/vnd.transitive.document+json':
                return $this->route->getDocument();
            break;
            case 'application/vnd.transitive.document+xml':
                return $this->route->getDocument()->asXML('document');
            break;
            case 'application/vnd.transitive.document+yaml':
                return $this->route->getDocument()->asYAML();
            break;
            case 'application/vnd.transitive.head+json':
                return $this->route->getHead()->asJson();
            break;
            case 'application/vnd.transitive.head+xml':
                return $this->route->getHead()->asXML('head');
            break;
            case 'application/vnd.transitive.head+yaml':
                return $this->route->getHead()->asYAML();
            break;
            case 'application/vnd.transitive.content+xhtml': case 'application/vnd.transitive.content+html':
                return $this->route->getContent();
            break;
            case 'application/vnd.transitive.content+css':
                return $this->route->getView()->getStylesContent();
            break;
            case 'application/vnd.transitive.content+javascript':
                return $this->route->getView()->getScriptsContent();
            break;
            case 'application/vnd.transitive.content+json':
                return $this->route->getContent()->asJson();
            break;
            case 'application/vnd.transitive.content+xml':
                return $this->route->getContent()->asXML('content');
            break;
            case 'application/vnd.transitive.content+yaml':
                return $this->route->getContent()->asYAML();
            break;

            case 'text/plain':
                return $this->layout->getContent()->asString();
            break;

            case 'application/json':
                if($this->route->hasContent('application/json'))
                    return $this->route->getContentByType('application/json')->asJson();
                elseif(300 >= http_response_code()) {
                    http_response_code(204);
                    $_SERVER['REDIRECT_STATUS'] = 204;

                    return '';
                }
            break;
            case 'application/xml':
                if($this->route->hasContent('application/xml', $contentKey))
                    return $this->route->getContent('application/xml', $contentKey)->asJson();
                elseif(300 >= http_response_code()) {
                    http_response_code(204);
                    $_SERVER['REDIRECT_STATUS'] = 204;

                    return '';
                }

            break;

            default:
                return $this->layout->getContent();
        }
    }

    public static function setDefaultHttpErrorRoute(Routing\Route $route): void
    {
        self::$defaultHttpErrorRoute = $route;
    }

    public function setHttpErrorRoute(Routing\Route $route): void
    {
        $this->httpErrorRoute = $route;
    }
}

Front::setDefaultHttpErrorRoute(new Routing\Route(dirname(getcwd()).'/presenters/genericHttpErrorHandler.php', dirname(getcwd()).'/views/genericHttpErrorHandler.php', null, [], Front::defaultViewClassName));
